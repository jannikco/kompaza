<script>
function automationForm(initial) {
    return {
        name: initial.name || '',
        postUrl: initial.postUrl || '',
        keywords: initial.keywords || '',
        autoReplyEnabled: initial.autoReplyEnabled !== false,
        autoReplyTemplate: initial.autoReplyTemplate || 'Sent to your DMs! Check your inbox',
        nonFirstReply: initial.nonFirstReply || '',
        autoDmEnabled: initial.autoDmEnabled !== false,
        dmTemplate: initial.dmTemplate || 'Hi {{firstName}}, thanks for your interest! Here is the resource: {{lead_magnet_url}}',
        followUpEnabled: !!initial.followUpEnabled,
        followUpTemplate: initial.followUpTemplate || 'Hi {{firstName}}, just checking you got the link: {{lead_magnet_url}}',
        validating: false,
        postValidated: false,
        postUrn: '',
        postError: '',
        loadingPosts: false,
        recentPosts: [],
        postsError: '',
        suggesting: false,
        suggestError: '',

        loadRecentPosts() {
            this.loadingPosts = true;
            this.postsError = '';
            fetch('/api/connectpilot/recent-posts')
                .then(r => r.json())
                .then(data => {
                    this.loadingPosts = false;
                    if (data.success) {
                        this.recentPosts = data.posts || [];
                        if (!this.recentPosts.length) this.postsError = 'No recent posts found on this account.';
                    } else {
                        this.postsError = data.error || 'Could not load posts.';
                    }
                })
                .catch(() => {
                    this.loadingPosts = false;
                    this.postsError = 'Network error.';
                });
        },

        pickPost(post) {
            this.postUrl = post.share_url || ('https://www.linkedin.com/feed/update/' + post.post_urn + '/');
            this.postUrn = post.post_urn;
            this.postValidated = true;
            this.postError = '';
        },

        validatePost() {
            if (!this.postUrl) {
                this.postError = 'Please enter a post URL first.';
                return;
            }
            this.validating = true;
            this.postError = '';
            this.postValidated = false;
            fetch('/api/connectpilot/validate-post', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ post_url: this.postUrl })
            })
            .then(r => r.json())
            .then(data => {
                this.validating = false;
                if (data.success) {
                    this.postValidated = true;
                    this.postUrn = data.post_urn;
                } else {
                    this.postError = data.error || 'Validation failed.';
                }
            })
            .catch(() => {
                this.validating = false;
                this.postError = 'Network error. Please try again.';
            });
        },

        suggestSettings() {
            const text = this.recentPosts.find(p => p.share_url === this.postUrl || p.post_urn === this.postUrn)?.commentary
                || this.name
                || this.keywords;
            if (!text) {
                this.suggestError = 'Pick a recent post or type a name first so there is something to draft from.';
                return;
            }
            this.suggesting = true;
            this.suggestError = '';
            fetch('/api/connectpilot/suggest-automation', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ post_text: text })
            })
            .then(r => r.json())
            .then(data => {
                this.suggesting = false;
                if (!data.success) {
                    this.suggestError = data.error || 'Suggestion failed.';
                    return;
                }
                const s = data.suggestion;
                if (s.name && !this.name) this.name = s.name;
                if (s.keywords) this.keywords = s.keywords.join(', ');
                if (s.auto_reply_template) this.autoReplyTemplate = s.auto_reply_template;
                if (s.non_first_degree_reply_template) this.nonFirstReply = s.non_first_degree_reply_template;
                if (s.dm_template) this.dmTemplate = s.dm_template;
                if (s.follow_up_template) this.followUpTemplate = s.follow_up_template;
            })
            .catch(() => {
                this.suggesting = false;
                this.suggestError = 'Network error.';
            });
        },

        insertVariable(variable) {
            const textarea = document.getElementById('dm_template');
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            this.dmTemplate = this.dmTemplate.substring(0, start) + variable + this.dmTemplate.substring(end);
            this.$nextTick(() => {
                textarea.focus();
                textarea.selectionStart = textarea.selectionEnd = start + variable.length;
            });
        }
    };
}
</script>
