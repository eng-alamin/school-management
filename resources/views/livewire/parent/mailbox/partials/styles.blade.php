{{-- resources/views/livewire/admin/mailbox/partials/styles.blade.php --}}
{{-- Shared styles for the whole Mailbox module. Included once per page. --}}

@once
<style>
/* ═══════════════════════════════════════════════════════════════
   MAILBOX MODULE — SHARED STYLES (Bootstrap 5 friendly)
   ═══════════════════════════════════════════════════════════════ */

:root {
    --mb-primary: #ec4899;
    --mb-primary-dark: #db2777;
    --mb-primary-light: #f472b6;
    /* --mb-primary: #4f46e5;
    --mb-primary-dark: #4338ca;
    --mb-primary-light: #818cf8; */
    --mb-success: #10b981;
    --mb-warning: #f59e0b;
    --mb-danger: #ef4444;
    --mb-muted: #94a3b8;
    --mb-border: #e6e9f0;
    --mb-bg-soft: #f8fafc;
    --mb-text: #1e293b;
    --mb-radius: 14px;
    --mb-radius-sm: 10px;
    --mb-shadow: 0 4px 24px rgba(15, 23, 42, .06);
}

/* ── Layout ────────────────────────────────────────────────── */
.mailbox-wrapper {
    display: flex;
    background: #fff;
    border-radius: var(--mb-radius);
    box-shadow: var(--mb-shadow);
    overflow: hidden;
    min-height: 80vh;
    border: 1px solid var(--mb-border);
}

/* ── Sidebar ───────────────────────────────────────────────── */
.mailbox-sidebar {
    width: 230px;
    min-width: 230px;
    padding: 22px 14px;
    display: flex;
    flex-direction: column;
    border-right: 1px solid var(--mb-border);
    background: var(--mb-bg-soft);
}
.compose-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    margin: 0 0 22px;
    padding: 12px 18px;
    color: #fff !important;
    background: linear-gradient(135deg, var(--mb-primary), var(--mb-primary-light));
    border-radius: var(--mb-radius-sm);
    font-weight: 600;
    font-size: .92rem;
    text-decoration: none;
    box-shadow: 0 6px 16px rgba(79, 70, 229, .28);
    transition: transform .15s, box-shadow .15s;
    border: none;
}
.compose-btn:hover,
.compose-btn.active {
    transform: translateY(-1px);
    box-shadow: 0 8px 20px rgba(79, 70, 229, .38);
    color: #fff !important;
}
.sidebar-nav { display: flex; flex-direction: column; gap: 2px; }
.nav-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    border-radius: var(--mb-radius-sm);
    color: #64748b !important;
    text-decoration: none;
    font-size: .9rem;
    font-weight: 500;
    transition: background .15s, color .15s;
}
.nav-item i { width: 16px; text-align: center; font-size: .9rem; }
.nav-item:hover { background: #eef2ff; color: var(--mb-primary-dark) !important; }
.nav-item.active {
    background: #eef2ff;
    color: var(--mb-primary) !important;
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
    color: var(--mb-text);
    margin: 0;
}

/* ── Search box ───────────────────────────────────────────── */
.search-box { position: relative; }
.search-icon {
    position: absolute;
    left: 13px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--mb-muted);
    font-size: .85rem;
    pointer-events: none;
}
.search-input {
    padding-left: 36px;
    border-radius: 50px;
    border: 1px solid var(--mb-border);
    font-size: .88rem;
    width: 250px;
    background: var(--mb-bg-soft);
}
.search-input:focus {
    box-shadow: 0 0 0 3px rgba(79, 70, 229, .14);
    border-color: var(--mb-primary);
    background: #fff;
}

/* ── Message list ─────────────────────────────────────────── */
.message-list {
    border: 1px solid var(--mb-border);
    border-radius: var(--mb-radius-sm);
    overflow: hidden;
}
.message-row {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 18px;
    border-bottom: 1px solid #f1f5f9;
    cursor: pointer;
    transition: background .15s;
    background: #fff;
}
.message-row:last-child { border-bottom: none; }
.message-row:hover { background: var(--mb-bg-soft); }
.message-row.unread { background: #eef2ff; border-left: 3px solid var(--mb-primary); }
.message-row.trashed { opacity: .75; }

/* ── Avatars ──────────────────────────────────────────────── */
.msg-avatar,
.meta-avatar {
    width: 40px; height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--mb-primary), var(--mb-primary-light));
    color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: .9rem;
    flex-shrink: 0;
}

.meta-avatar { width: 44px; height: 44px; }
.sent-avatar    { background: linear-gradient(135deg, #95044c, #f26bb1); }
.trashed-avatar { background: linear-gradient(135deg, #94a3b8, #cbd5e1); }

/* ── Row info ─────────────────────────────────────────────── */
.msg-info { flex: 1; min-width: 0; }
.msg-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 3px; gap: 8px; }
.msg-from { font-weight: 600; font-size: .9rem; color: var(--mb-text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.msg-date { font-size: .76rem; color: var(--mb-muted); white-space: nowrap; }
.msg-subject { font-size: .88rem; color: #374151; margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.msg-excerpt  { font-size: .8rem; color: var(--mb-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.message-row.unread .msg-subject { font-weight: 700; color: var(--mb-text); }

/* ── Row action buttons ───────────────────────────────────── */
.msg-actions { display: flex; align-items: center; gap: 4px; flex-shrink: 0; }
.msg-icon-btn {
    width: 32px; height: 32px;
    display: inline-flex; align-items: center; justify-content: center;
    border: none;
    background: transparent;
    border-radius: 50%;
    color: var(--mb-muted);
    font-size: .88rem;
    transition: background .15s, color .15s, transform .15s;
    cursor: pointer;
}
.msg-icon-btn:hover { background: #eef2ff; transform: scale(1.08); }
.msg-icon-btn.text-warning { color: var(--mb-warning); }
.msg-icon-btn.text-danger:hover { background: #fee2e2; color: var(--mb-danger); }
.msg-icon-btn.text-success:hover { background: #dcfce7; color: var(--mb-success); }

/* ── Message view ─────────────────────────────────────────── */
.message-view {
    background: var(--mb-bg-soft);
    border: 1px solid var(--mb-border);
    border-radius: var(--mb-radius-sm);
    overflow: hidden;
}
.message-view-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 20px;
    background: #fff;
    border-bottom: 1px solid var(--mb-border);
    flex-wrap: wrap;
    gap: 8px;
}
.message-view-actions { display: flex; gap: 8px; flex-wrap: wrap; }
.message-view-body { padding: 26px 28px; }
.message-subject { font-size: 1.15rem; font-weight: 700; color: var(--mb-text); margin-bottom: 18px; }
.message-meta { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 4px; }
.message-body-text { line-height: 1.75; color: #374151; white-space: pre-wrap; word-break: break-word; }

/* ── Reply form ───────────────────────────────────────────── */
.reply-form { border-top: 1px solid var(--mb-border); padding: 20px 28px 24px; background: #fff; }

/* ── Trash notice ─────────────────────────────────────────── */
.trash-notice {
    background: #fef3c7;
    border: 1px solid #fcd34d;
    border-radius: var(--mb-radius-sm);
    padding: 10px 16px;
    font-size: .88rem;
    color: #92400e;
}

/* ── Empty state ──────────────────────────────────────────── */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 80px 20px;
    color: var(--mb-muted);
    text-align: center;
}
.empty-state i { font-size: 3rem; margin-bottom: 16px; opacity: .35; }
.empty-state p { font-size: .95rem; margin: 0; }

/* ── Compose form ─────────────────────────────────────────── */
.compose-form-card {
    background: #fff;
    border: 1px solid var(--mb-border);
    border-radius: var(--mb-radius-sm);
    padding: 26px 28px;
    box-shadow: var(--mb-shadow);
}
.btn-send {
    background: linear-gradient(135deg, var(--mb-primary), var(--mb-primary-light));
    color: #fff;
    border: none;
    padding: 10px 22px;
    border-radius: 50px;
    font-weight: 600;
    box-shadow: 0 6px 16px rgba(79, 70, 229, .28);
}
.btn-send:hover { color: #fff; box-shadow: 0 8px 20px rgba(79, 70, 229, .38); }
.compose-actions { display: flex; align-items: center; }

/* Selected recipient badge */
.selected-user-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 14px;
    background: #eef2ff;
    color: var(--mb-primary-dark);
    border-radius: 50px;
    font-weight: 600;
    font-size: .88rem;
}
.btn-clear-user {
    border: none;
    background: transparent;
    color: var(--mb-primary-dark);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 20px; height: 20px;
    border-radius: 50%;
    font-size: .75rem;
    cursor: pointer;
}
.btn-clear-user:hover { background: rgba(79, 70, 229, .15); }

/* Recipient suggestions dropdown */
.suggestions-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    margin-top: 4px;
    background: #fff;
    border: 1px solid var(--mb-border);
    border-radius: var(--mb-radius-sm);
    box-shadow: var(--mb-shadow);
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
.suggestion-item:hover { background: var(--mb-bg-soft); }
.suggestion-avatar {
    width: 32px; height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--mb-primary), var(--mb-primary-light));
    color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: .78rem;
    flex-shrink: 0;
}
.suggestion-info { display: flex; flex-direction: column; min-width: 0; }
.suggestion-name { font-size: .88rem; font-weight: 600; color: var(--mb-text); }
.suggestion-email { font-size: .76rem; color: var(--mb-muted); }

/* ── Dark mode (scoped to mailbox only) ───────────────────── */
body.dark-mode .mailbox-wrapper {
    background: #0f172a;
    border-color: rgba(255,255,255,.08);
}
body.dark-mode .mailbox-sidebar { background: #0b1220; border-color: rgba(255,255,255,.08); }
body.dark-mode .nav-item { color: #94a3b8 !important; }
body.dark-mode .nav-item:hover,
body.dark-mode .nav-item.active { background: rgba(99,102,241,.16); color: #a5b4fc !important; }
body.dark-mode .mailbox-header h4 { color: #e2e8f0; }
body.dark-mode .message-list,
body.dark-mode .message-view,
body.dark-mode .compose-form-card,
body.dark-mode .suggestions-dropdown { background: #0f172a; border-color: rgba(255,255,255,.08); }
body.dark-mode .message-row,
body.dark-mode .message-view-header,
body.dark-mode .reply-form { background: #0f172a; border-color: rgba(255,255,255,.06); }
body.dark-mode .message-row:hover { background: #16213a; }
body.dark-mode .message-row.unread { background: rgba(99,102,241,.12); }
body.dark-mode .msg-from,
body.dark-mode .msg-subject,
body.dark-mode .message-subject,
body.dark-mode .message-body-text { color: #e2e8f0; }
body.dark-mode .search-input { background: #16213a; border-color: rgba(255,255,255,.1); color: #e2e8f0; }
body.dark-mode .suggestion-item:hover { background: #16213a; }

body.dark-mode .mailbox-wrapper .form-control,
body.dark-mode .mailbox-wrapper textarea.form-control {
    background: #16213a !important;
    border-color: rgba(255,255,255,.12) !important;
    color: #e2e8f0 !important;
}
body.dark-mode .mailbox-wrapper .form-control::placeholder { color: #64748b; }

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