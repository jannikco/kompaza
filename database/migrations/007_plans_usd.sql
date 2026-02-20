-- Migration 007: Switch plan prices from DKK to USD
-- Rename columns and update seed data with Kajabi-inspired USD pricing

ALTER TABLE plans CHANGE price_monthly_dkk price_monthly_usd DECIMAL(10,2) NOT NULL DEFAULT 0;
ALTER TABLE plans CHANGE price_yearly_dkk price_yearly_usd DECIMAL(10,2) DEFAULT NULL;

-- Update seed plan prices to Kajabi-inspired USD tiers
UPDATE plans SET price_monthly_usd = 79.00, price_yearly_usd = 780.00 WHERE slug = 'starter';
UPDATE plans SET price_monthly_usd = 149.00, price_yearly_usd = 1500.00 WHERE slug = 'growth';
UPDATE plans SET price_monthly_usd = 299.00, price_yearly_usd = 2988.00 WHERE slug = 'enterprise';
