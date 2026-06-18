-- Run this once to add the is_read column if it doesn't already exist.
-- Safe to run multiple times (uses IF NOT EXISTS via stored procedure pattern).

ALTER TABLE messages
    ADD COLUMN IF NOT EXISTS is_read TINYINT(1) NOT NULL DEFAULT 0;

-- Index speeds up the unread-count query in the sidebar
CREATE INDEX IF NOT EXISTS idx_messages_receiver_read
    ON messages (receiver_id, is_read);

-- Backfill: treat all existing messages as already read
UPDATE messages SET is_read = 1 WHERE is_read IS NULL OR is_read = 0;