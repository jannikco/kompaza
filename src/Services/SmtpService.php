<?php

namespace App\Services;

class SmtpService {
    private string $host;
    private int $port;
    private string $username;
    private string $password;
    private string $encryption;

    public function __construct(string $host, int $port, string $username, string $password, string $encryption = 'tls') {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        $this->encryption = $encryption;
    }

    /**
     * Send a transactional email via raw SMTP.
     *
     * @param array|string $to         Recipient email (string) or array with 'email' and optional 'name'
     * @param string       $subject    Email subject
     * @param string       $htmlContent HTML body
     * @param string|null  $fromEmail  Sender email
     * @param string|null  $fromName   Sender name
     * @return array ['success' => true]
     * @throws \Exception on SMTP error
     */
    public function sendTransactionalEmail(
        $to,
        string $subject,
        string $htmlContent,
        ?string $fromEmail = null,
        ?string $fromName = null
    ): array {
        $fromEmail = $fromEmail ?? EmailHelper::resolveFromEmail();
        $fromName = $fromName ?? EmailHelper::resolveFromName();

        // Normalize $to to email string
        if (is_array($to)) {
            $toEmail = $to['email'] ?? $to[0]['email'] ?? '';
            $toName = $to['name'] ?? $to[0]['name'] ?? '';
        } else {
            $toEmail = $to;
            $toName = '';
        }

        $message = $this->buildMimeMessage($fromEmail, $fromName, $toEmail, $toName, $subject, $htmlContent);
        $this->smtpSend($fromEmail, $toEmail, $message);

        return ['success' => true];
    }

    /**
     * Send lead magnet delivery email with download link.
     */
    public function sendLeadMagnetEmail(string $email, string $name, array $leadMagnet, string $downloadUrl): array {
        $built = EmailHelper::buildLeadMagnetEmail($email, $name, $leadMagnet, $downloadUrl);

        return $this->sendTransactionalEmail(
            ['email' => $email, 'name' => $name],
            $built['subject'],
            $built['htmlContent']
        );
    }

    /**
     * Check if this service instance is properly configured.
     */
    public function isConfigured(): bool {
        return !empty($this->host) && !empty($this->username) && !empty($this->password);
    }

    /**
     * Build RFC 2822 MIME message with HTML content-type.
     */
    private function buildMimeMessage(
        string $fromEmail,
        string $fromName,
        string $toEmail,
        string $toName,
        string $subject,
        string $htmlContent
    ): string {
        $boundary = md5(uniqid(time()));
        $from = $fromName ? "=?UTF-8?B?" . base64_encode($fromName) . "?= <{$fromEmail}>" : $fromEmail;
        $to = $toName ? "=?UTF-8?B?" . base64_encode($toName) . "?= <{$toEmail}>" : $toEmail;

        $headers = "From: {$from}\r\n";
        $headers .= "To: {$to}\r\n";
        $headers .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n";
        $headers .= "Date: " . date('r') . "\r\n";
        $headers .= "Message-ID: <" . uniqid() . "@" . $this->host . ">\r\n";
        $headers .= "\r\n";

        // Plain text fallback
        $plainText = strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], "\n", $htmlContent));

        $body = "--{$boundary}\r\n";
        $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $body .= chunk_split(base64_encode($plainText)) . "\r\n";
        $body .= "--{$boundary}\r\n";
        $body .= "Content-Type: text/html; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $body .= chunk_split(base64_encode($htmlContent)) . "\r\n";
        $body .= "--{$boundary}--\r\n";

        return $headers . $body;
    }

    /**
     * Send email via raw SMTP protocol.
     */
    private function smtpSend(string $from, string $to, string $message): void {
        $errno = 0;
        $errstr = '';
        $timeout = 30;

        // Connect
        if ($this->encryption === 'ssl' || $this->port === 465) {
            $socket = stream_socket_client(
                "ssl://{$this->host}:{$this->port}",
                $errno, $errstr, $timeout,
                STREAM_CLIENT_CONNECT,
                stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true]])
            );
        } else {
            $socket = stream_socket_client(
                "tcp://{$this->host}:{$this->port}",
                $errno, $errstr, $timeout
            );
        }

        if (!$socket) {
            throw new \Exception("SMTP connection failed: {$errstr} ({$errno})");
        }

        stream_set_timeout($socket, $timeout);

        // Read greeting
        $this->smtpReadResponse($socket, 220);

        // EHLO
        $this->smtpCommand($socket, "EHLO " . gethostname(), 250);

        // STARTTLS for port 587 with tls encryption
        if ($this->encryption === 'tls' && $this->port !== 465) {
            $this->smtpCommand($socket, "STARTTLS", 220);
            $crypto = stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT);
            if (!$crypto) {
                throw new \Exception("SMTP STARTTLS failed");
            }
            // Re-EHLO after STARTTLS
            $this->smtpCommand($socket, "EHLO " . gethostname(), 250);
        }

        // AUTH LOGIN
        $this->smtpCommand($socket, "AUTH LOGIN", 334);
        $this->smtpCommand($socket, base64_encode($this->username), 334);
        $this->smtpCommand($socket, base64_encode($this->password), 235);

        // MAIL FROM
        $this->smtpCommand($socket, "MAIL FROM:<{$from}>", 250);

        // RCPT TO
        $this->smtpCommand($socket, "RCPT TO:<{$to}>", 250);

        // DATA
        $this->smtpCommand($socket, "DATA", 354);

        // Send message body — dot-stuff lines starting with a period
        $lines = explode("\r\n", $message);
        foreach ($lines as $line) {
            if (isset($line[0]) && $line[0] === '.') {
                $line = '.' . $line;
            }
            fwrite($socket, $line . "\r\n");
        }
        $this->smtpCommand($socket, ".", 250);

        // QUIT
        fwrite($socket, "QUIT\r\n");
        fclose($socket);
    }

    /**
     * Send an SMTP command and verify the response code.
     */
    private function smtpCommand($socket, string $command, int $expectedCode): string {
        fwrite($socket, $command . "\r\n");
        return $this->smtpReadResponse($socket, $expectedCode);
    }

    /**
     * Read SMTP response and verify expected code.
     */
    private function smtpReadResponse($socket, int $expectedCode): string {
        $response = '';
        while ($line = fgets($socket, 515)) {
            $response .= $line;
            // Multi-line responses have a dash after the code (e.g., "250-...")
            // The final line has a space (e.g., "250 OK")
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
            // Also break if line is exactly 3 chars + CRLF
            if (strlen(trim($line)) === 3) {
                break;
            }
        }

        $code = (int)substr($response, 0, 3);
        if ($code !== $expectedCode) {
            throw new \Exception("SMTP error: expected {$expectedCode}, got {$code}. Response: " . trim($response));
        }

        return $response;
    }
}
