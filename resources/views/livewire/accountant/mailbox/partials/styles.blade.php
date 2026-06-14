{{-- resources/views/livewire/accountant/mailbox/partials/styles.blade.php --}}
{{-- Include this at the bottom of every mailbox blade view --}}

@once
<style>
/* ═══════════════════════════════════════════════════════════════
   MAILBOX — SHARED STYLES
   ═══════════════════════════════════════════════════════════════ */

/* ── Layout ────────────────────────────────────────────────── */
.mailbox-wrapper {
    display: flex;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 20px rgba(0,0,0,.08);
    overflow: hidden;
    min-height: 80vh;
}

/* ── Sidebar ────────────────────────────────────────────────── */
.mailbox-sidebar {
    width: 220px;
    min-width: 220px;
    /* background: #1e293b; */
    padding: 24px 0;
    display: flex;
    flex-direction: column;
}
.compose-btn {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0 16px 20px;
    padding: 12px 18px;
    background: #3b82f6;
    color: #fff !important;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    transition: background .2s;
}
.compose-btn:hover, .compose-btn.active { background: #2563eb; }
.sidebar-nav { display: flex; flex-direction: column; }
.nav-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 11px 24px;
    color: #94a3b8 !important;
    text-decoration: none;
    font-size: .92rem;
    transition: background .15s, color .15s;
}
.nav-item i { width: 16px; text-align: center; }
/* .nav-item:hover { background: rgba(255,255,255,.06); color: #fff !important; } */
.nav-item.active { background: rgba(59,130,246,.2); color: #60a5fa !important; border-right: 3px solid #3b82f6; }
.nav-item .badge { margin-left: auto; font-size: .7rem; padding: 3px 7px; border-radius: 10px; }
.badge-primary { background: #3b82f6; color: #fff; }
.badge-warning  { background: #f59e0b; color: #fff; }
.badge-danger   { background: #ef4444; color: #fff; }

/* ── Content area ───────────────────────────────────────────── */
.mailbox-content { flex: 1; padding: 28px 32px; overflow-y: auto; }

/* ── Page header ────────────────────────────────────────────── */
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
    color: #1e293b;
    margin: 0;
}

/* ── Search box ─────────────────────────────────────────────── */
.search-box { position: relative; }
.search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: .85rem;
}
.search-input {
    padding-left: 34px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    font-size: .88rem;
    width: 240px;
}
.search-input:focus { box-shadow: 0 0 0 3px rgba(59,130,246,.15); border-color: #3b82f6; }

/* ── Message list ───────────────────────────────────────────── */
.message-list {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
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
.message-row:hover { background: #f8fafc; }
.message-row.unread { background: #eff6ff; border-left: 3px solid #3b82f6; }
.message-row.trashed { opacity: .75; }

/* ── Message avatars ────────────────────────────────────────── */
.msg-avatar {
    width: 40px; height: 40px;
    border-radius: 50%;
    background: #3b82f6;
    color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: .9rem;
    flex-shrink: 0;
}
.sent-avatar   { background: #10b981; }
.trashed-avatar{ background: #94a3b8; }

/* ── Message row info ───────────────────────────────────────── */
.msg-info { flex: 1; min-width: 0; }
.msg-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 3px; }
.msg-from { font-weight: 600; font-size: .9rem; color: #1e293b; }
.msg-date { font-size: .78rem; color: #94a3b8; white-space: nowrap; }
.msg-subject { font-size: .88rem; color: #374151; margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.msg-excerpt  { font-size: .8rem; color: #94a3b8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.message-row.unread .msg-subject { font-weight: 700; color: #1e293b; }

/* ── Row actions ────────────────────────────────────────────── */
.msg-actions { display: flex; align-items: center; font-size: .95rem; flex-shrink: 0; }
.msg-actions i { cursor: pointer; transition: transform .15s; }
.msg-actions i:hover { transform: scale(1.2); }

/* ── Message view ───────────────────────────────────────────── */
.message-view {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
}
.message-view-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 20px;
    background: #fff;
    border-bottom: 1px solid #e2e8f0;
    flex-wrap: wrap;
    gap: 8px;
}
.message-view-actions { display: flex; gap: 8px; flex-wrap: wrap; }
.message-view-body { padding: 24px 28px; }
.message-subject { font-size: 1.15rem; font-weight: 700; color: #1e293b; margin-bottom: 16px; }
.message-meta { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 4px; }
.meta-avatar {
    width: 42px; height: 42px;
    border-radius: 50%;
    background: #3b82f6;
    color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700;
    flex-shrink: 0;
}
.message-body-text { line-height: 1.7; color: #374151; white-space: pre-wrap; word-break: break-word; }

/* ── Reply form ─────────────────────────────────────────────── */
.reply-form {
    border-top: 1px solid #e2e8f0;
    padding: 20px 28px 24px;
    background: #fff;
}

/* ── Trash notice ───────────────────────────────────────────── */
.trash-notice {
    background: #fef3c7;
    border: 1px solid #fcd34d;
    border-radius: 8px;
    padding: 10px 16px;
    font-size: .88rem;
    color: #92400e;
}

/* ── Empty state ────────────────────────────────────────────── */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 80px 20px;
    color: #94a3b8;
    text-align: center;
}
.empty-state i { font-size: 3rem; margin-bottom: 16px; opacity: .4; }
.empty-state p { font-size: .95rem; margin: 0; }

/* ── Responsive ─────────────────────────────────────────────── */
@media (max-width: 768px) {
    .mailbox-sidebar { width: 60px; min-width: 60px; }
    .mailbox-sidebar span,
    .compose-btn span { display: none; }
    .compose-btn { justify-content: center; padding: 12px; }
    .nav-item { justify-content: center; padding: 12px; }
    .nav-item .badge { display: none; }
    .mailbox-content { padding: 16px; }
    .search-input { width: 160px; }
}
</style>
@endonce
