{{-- resources/views/livewire/admin/mailbox/partials/styles.blade.php --}}
{{-- Shared styles for the whole Mailbox module. Included once per page. --}}
{{-- Refactored to consume the global theme.css design-token system (--primary,
     --card, --border, --surface-2, etc.) instead of a standalone --mb-* set,
     so this module inherits dark-mode / light-mode automatically. --}}

@once
<style>
/* ═══════════════════════════════════════════════════════════════
   MAILBOX MODULE — SHARED STYLES (Bootstrap 5 friendly)
   Uses tokens defined in theme.css :root / body.dark-mode — no local
   color variables are declared here.
   ═══════════════════════════════════════════════════════════════ */

/* ── Layout ────────────────────────────────────────────────── */
.mailbox-wrapper {
    display: flex;
    background: var(--card);
    border-radius: var(--radius-card);
    box-shadow: var(--shadow);
    overflow: hidden;
    min-height: 80vh;
    border: 1px solid var(--border);
}

/* ── Sidebar ───────────────────────────────────────────────── */
.mailbox-sidebar {
    width: 230px;
    min-width: 230px;
    padding: 22px 14px;
    display: flex;
    flex-direction: column;
    border-right: 1px solid var(--border);
    background: var(--surface-2);
}
.compose-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    margin: 0 0 22px;
    padding: 12px 18px;
    color: var(--white) !important;
    background: linear-gradient(135deg, var(--grad-dark-start), var(--grad-dark-end));
    border-radius: var(--radius-sm);
    font-weight: 600;
    font-size: .92rem;
    text-decoration: none;
    box-shadow: var(--shadow-md);
    transition: transform .15s, box-shadow .15s;
    border: none;
}
.compose-btn:hover,
.compose-btn.active {
    transform: translateY(-1px);
    box-shadow: var(--shadow-hover);
    color: var(--white) !important;
}
.sidebar-nav { display: flex; flex-direction: column; gap: 2px; }
.nav-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    border-radius: var(--radius-sm);
    color: var(--muted) !important;
    text-decoration: none;
    font-size: .9rem;
    font-weight: 500;
    transition: background .15s, color .15s;
}
.nav-item i { width: 16px; text-align: center; font-size: .9rem; }
.nav-item:hover { background: var(--primary-100); color: var(--primary-dark) !important; }
.nav-item.active {
    background: var(--primary-100);
    color: var(--primary) !important;
    font-weight: 700;
}
.nav-item .badge { margin-left: auto; font-size: .68rem; padding: 3px 7px; border-radius: 10px; font-weight: 700; }

/* ── Content area ─────────────────────────────────────────── */
.mailbox-content { flex: 1; padding: 26px 30px; overflow-y: auto; }

/* ── Page header ──────────────────────────────────────────── */
.mailbox-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 12px;
}
.mailbox-header h4 {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--heading);
    margin: 0;
}

/* ── Search box ───────────────────────────────────────────── */
.search-box { position: relative; }
.search-icon {
    position: absolute;
    left: 13px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--muted);
    font-size: .85rem;
    pointer-events: none;
}
.search-input {
    padding-left: 36px;
    border-radius: var(--radius-pill);
    border: 1px solid var(--border);
    font-size: .88rem;
    width: 250px;
    background: var(--surface-2);
    color: var(--ink);
}
.search-input:focus {
    box-shadow: 0 0 0 3px var(--primary-100);
    border-color: var(--primary);
    background: var(--card);
}

/* ── Message list ─────────────────────────────────────────── */
.message-list {
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    overflow: hidden;
}
.message-row {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 18px;
    border-bottom: 1px solid var(--divider);
    cursor: pointer;
    transition: background .15s;
    background: var(--card);
}
.message-row:last-child { border-bottom: none; }
.message-row:hover { background: var(--surface-2); }
.message-row.unread { background: var(--primary-50); border-left: 3px solid var(--primary); }
.message-row.trashed { opacity: .75; }

/* ── Avatars ──────────────────────────────────────────────── */
.msg-avatar,
.meta-avatar {
    width: 40px; height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: var(--white);
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: .9rem;
    flex-shrink: 0;
}

.meta-avatar { width: 44px; height: 44px; }
.sent-avatar    { background: linear-gradient(135deg, var(--pink), var(--pink-light)); }
.trashed-avatar { background: linear-gradient(135deg, var(--muted-2), var(--scrollbar-thumb)); }

/* ── Row info ─────────────────────────────────────────────── */
.msg-info { flex: 1; min-width: 0; }
.msg-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 3px; gap: 8px; }
.msg-from { font-weight: 600; font-size: .9rem; color: var(--heading); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.msg-date { font-size: .76rem; color: var(--muted); white-space: nowrap; }
.msg-subject { font-size: .88rem; color: var(--text-1); margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.msg-excerpt  { font-size: .8rem; color: var(--muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.message-row.unread .msg-subject { font-weight: 700; color: var(--heading); }

/* ── Row action buttons ───────────────────────────────────── */
.msg-actions { display: flex; align-items: center; gap: 4px; flex-shrink: 0; }
.msg-icon-btn {
    width: 32px; height: 32px;
    display: inline-flex; align-items: center; justify-content: center;
    border: none;
    background: transparent;
    border-radius: 50%;
    color: var(--muted);
    font-size: .88rem;
    transition: background .15s, color .15s, transform .15s;
    cursor: pointer;
}
.msg-icon-btn:hover { background: var(--primary-100); transform: scale(1.08); }
.msg-icon-btn.text-warning { color: var(--warning); }
.msg-icon-btn.text-danger:hover { background: var(--danger-100); color: var(--danger); }
.msg-icon-btn.text-success:hover { background: var(--success-100); color: var(--success); }

/* ── Message view ─────────────────────────────────────────── */
.message-view {
    background: var(--surface-2);
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    overflow: hidden;
}
.message-view-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 20px;
    background: var(--card);
    border-bottom: 1px solid var(--border);
    flex-wrap: wrap;
    gap: 8px;
}
.message-view-actions { display: flex; gap: 8px; flex-wrap: wrap; }
.message-view-body { padding: 26px 28px; }
.message-subject { font-size: 1.15rem; font-weight: 700; color: var(--heading); margin-bottom: 18px; }
.message-meta { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 4px; }
.message-body-text { line-height: 1.75; color: var(--text-1); white-space: pre-wrap; word-break: break-word; }

/* ── Reply form ───────────────────────────────────────────── */
.reply-form { border-top: 1px solid var(--border); padding: 20px 28px 24px; background: var(--card); }

/* ── Trash notice ─────────────────────────────────────────── */
.trash-notice {
    background: var(--warning-100);
    border: 1px solid var(--warning);
    border-radius: var(--radius-sm);
    padding: 10px 16px;
    font-size: .88rem;
    color: var(--warning);
}

/* ── Empty state ──────────────────────────────────────────── */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 80px 20px;
    color: var(--muted);
    text-align: center;
}
.empty-state i { font-size: 3rem; margin-bottom: 16px; opacity: .35; }
.empty-state p { font-size: .95rem; margin: 0; }

/* ── Compose form ─────────────────────────────────────────── */
.compose-form-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    padding: 26px 28px;
    box-shadow: var(--shadow);
}
.btn-send {
    background: linear-gradient(135deg, var(--grad-dark-start), var(--grad-dark-end));
    color: var(--white);
    border: none;
    padding: 10px 22px;
    border-radius: var(--radius-pill);
    font-weight: 600;
    box-shadow: var(--shadow-md);
}
.btn-send:hover { color: var(--white); box-shadow: var(--shadow-hover); }
.compose-actions { display: flex; align-items: center; }

/* Selected recipient badge */
.selected-user-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 14px;
    background: var(--primary-100);
    color: var(--primary-dark);
    border-radius: var(--radius-pill);
    font-weight: 600;
    font-size: .88rem;
}
.btn-clear-user {
    border: none;
    background: transparent;
    color: var(--primary-dark);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 20px; height: 20px;
    border-radius: 50%;
    font-size: .75rem;
    cursor: pointer;
}
.btn-clear-user:hover { background: var(--hover-tint); }

/* Recipient suggestions dropdown */
.suggestions-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    margin-top: 4px;
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    box-shadow: var(--shadow);
    max-height: 260px;
    overflow-y: auto;
    z-index: 20;
}
.suggestion-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    cursor: pointer;
    transition: background .15s;
}
.suggestion-item:hover { background: var(--surface-2); }
.suggestion-avatar {
    width: 32px; height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: var(--white);
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: .78rem;
    flex-shrink: 0;
}
.suggestion-info { display: flex; flex-direction: column; min-width: 0; }
.suggestion-name { font-size: .88rem; font-weight: 600; color: var(--heading); }
.suggestion-email { font-size: .76rem; color: var(--muted); }

/* ── Dark mode ─────────────────────────────────────────────
   Not needed here: every color above is a var() from theme.css,
   and body.dark-mode already redefines --card, --border, --surface-2,
   --heading, --muted, --primary-100, etc. globally. The mailbox
   module inherits dark mode automatically with zero overrides. ── */

/* ── Responsive ───────────────────────────────────────────── */
@media (max-width: 768px) {
    .mailbox-sidebar { width: 64px; min-width: 64px; padding: 18px 8px; }
    .mailbox-sidebar span,
    .compose-btn span { display: none; }
    .compose-btn { padding: 12px; }
    .nav-item { justify-content: center; padding: 12px; }
    .nav-item .badge { display: none; }
    .mailbox-content { padding: 16px; }
    .search-input { width: 170px; }
}
</style>
@endonce