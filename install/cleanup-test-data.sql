-- ============================================================
-- Database Cleanup Script - Remove All Test Data
-- ============================================================
-- 
-- This script removes all testing/demo data while preserving:
-- âœ“ sys_appconfig (Configuration & ZATCA Settings)
-- âœ“ Email templates and settings
-- âœ“ System reference data
-- âœ“ All settings
--
-- Safe to run multiple times
-- ============================================================

SET FOREIGN_KEY_CHECKS=0;

-- ============================================================
-- Customer & Contact Data
-- ============================================================
TRUNCATE TABLE `crm_accounts`;
TRUNCATE TABLE `crm_customfieldsvalues`;

-- ============================================================
-- Invoice & Quote Data
-- ============================================================
TRUNCATE TABLE `sys_invoices`;
TRUNCATE TABLE `sys_invoiceitems`;
TRUNCATE TABLE `sys_quotes`;
TRUNCATE TABLE `sys_quote_items`;

-- ============================================================
-- Product Data
-- ============================================================
TRUNCATE TABLE `sys_items`;

-- ============================================================
-- Transaction & Payment Data
-- ============================================================
TRUNCATE TABLE `sys_transactions`;
TRUNCATE TABLE `sys_email_logs`;
TRUNCATE TABLE `sys_payments`;
TRUNCATE TABLE `account_balances`;

-- ============================================================
-- Access Logs & Activity
-- ============================================================
TRUNCATE TABLE `ib_invoice_access_log`;
TRUNCATE TABLE `sys_activity`;

-- ============================================================
-- Documents
-- ============================================================
TRUNCATE TABLE `sys_documents`;
TRUNCATE TABLE `ib_doc_rel`;

-- ============================================================
-- Shopping Cart
-- ============================================================
TRUNCATE TABLE `sys_cart`;

-- ============================================================
-- Ticket Data
-- ============================================================
TRUNCATE TABLE `sys_tickets`;
TRUNCATE TABLE `sys_ticket_attachments`;

-- ============================================================
-- CRM Data
-- ============================================================
TRUNCATE TABLE `crm_leads`;
TRUNCATE TABLE `app_notes`;

-- ============================================================
-- Contract Data
-- ============================================================
TRUNCATE TABLE `sys_contracts`;
TRUNCATE TABLE `sys_contract_items`;

-- ============================================================
-- Purchase Orders
-- ============================================================
TRUNCATE TABLE `sys_purchase_orders`;
TRUNCATE TABLE `sys_po_items`;

-- ============================================================
-- Employee & Attendance Data
-- ============================================================
TRUNCATE TABLE `employees`;
TRUNCATE TABLE `attendances`;

-- ============================================================
-- Expense Data
-- ============================================================
TRUNCATE TABLE `sys_expenses`;

-- ============================================================
-- SMS Data
-- ============================================================
TRUNCATE TABLE `app_sms`;

-- ============================================================
-- Credit Cards
-- ============================================================
TRUNCATE TABLE `credit_cards`;

-- ============================================================
-- Assets
-- ============================================================
TRUNCATE TABLE `ib_assets`;
TRUNCATE TABLE `assets`;

-- ============================================================
-- Project Data
-- ============================================================
TRUNCATE TABLE `sys_projects`;
TRUNCATE TABLE `sys_projects_items`;

-- ============================================================
-- Re-enable Foreign Key Checks
-- ============================================================
SET FOREIGN_KEY_CHECKS=1;

-- ============================================================
-- Verification Queries (Run to verify cleanup)
-- ============================================================
-- Verify ZATCA settings are SAFE:
-- SELECT setting, LEFT(value, 50) as value FROM sys_appconfig WHERE setting LIKE 'zatca%';

-- Verify test data is removed:
-- SELECT 'crm_accounts' as table_name, COUNT(*) as rows FROM crm_accounts
-- UNION ALL
-- SELECT 'sys_invoices', COUNT(*) FROM sys_invoices
-- UNION ALL
-- SELECT 'sys_items', COUNT(*) FROM sys_items;

-- ============================================================
-- Summary
-- ============================================================
-- âœ“ All test data removed
-- âœ“ ZATCA settings preserved
-- âœ“ Configuration intact
-- âœ“ System ready for fresh start
-- ============================================================

