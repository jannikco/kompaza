-- Homepage sections: allows tenants to customize homepage sections order, visibility, headings, and content
ALTER TABLE tenants ADD COLUMN homepage_sections LONGTEXT DEFAULT NULL AFTER homepage_template;
