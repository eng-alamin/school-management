<!doctype html>
<html lang="bn" data-theme="light" data-lang="bn">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>EMS – জাতীয় বিদ্যালয় ব্যবস্থাপনা সিস্টেম</title>

    <link rel="shortcut icon" href="{{ ($logo = setting('system_logo')) ? asset('storage/'.$logo) : asset('assets/img/default-logo.png') }}">
    
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css"
      rel="stylesheet"
    />
    <link
      href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Sora:wght@400;600;700;800&family=DM+Sans:wght@400;500;600&display=swap"
      rel="stylesheet"
    />
    <style>
      /*  DESIGN TOKENS – LIGHT & DARK*/
      :root {
        --primary: #198754;
        --primary-dark: #146c43;
        --primary-light: #d1e7dd;
        --secondary: #dc3545;
        --accent: #20c997;
        --accent2: #ffc107;

        --bg: #ffffff;
        --bg-alt: #f0faf4;
        --bg-card: #ffffff;
        --bg-card2: rgba(255, 255, 255, 0.85);
        --border: rgba(25, 135, 84, 0.12);
        --text: #1a2e1f;
        --text-muted: #6c757d;
        --text-soft: #4a5568;
        --navbar-bg: rgba(255, 255, 255, 0.94);
        --shadow: 0 4px 30px rgba(0, 0, 0, 0.07);
        --shadow-md: 0 8px 40px rgba(0, 0, 0, 0.1);
        --shadow-lg: 0 20px 60px rgba(0, 0, 0, 0.14);

        --hero-bg: linear-gradient(
          135deg,
          #d4edda 0%,
          #e9f7ef 40%,
          #f0fff8 100%
        );
        --footer-bg: #0b2b1a;
        --footer-text: #e0e0e0;
      }

      [data-theme="dark"] {
        --bg: #0d1f12;
        --bg-alt: #112418;
        --bg-card: #172b1d;
        --bg-card2: rgba(23, 43, 29, 0.9);
        --border: rgba(25, 135, 84, 0.22);
        --text: #e6f4ec;
        --text-muted: #8ca99a;
        --text-soft: #b0c9bb;
        --navbar-bg: rgba(13, 31, 18, 0.96);
        --shadow: 0 4px 30px rgba(0, 0, 0, 0.3);
        --shadow-md: 0 8px 40px rgba(0, 0, 0, 0.4);
        --shadow-lg: 0 20px 60px rgba(0, 0, 0, 0.5);

        --hero-bg: linear-gradient(
          135deg,
          #0d2515 0%,
          #0f2e1c 40%,
          #122918 100%
        );
        --footer-bg: #071409;
        --footer-text: #c0d9cb;
        --primary-light: #1a3d2b;
      }

      * {
        scroll-behavior: smooth;
        box-sizing: border-box;
      }

      body {
        font-family: "DM Sans", "Hind Siliguri", sans-serif;
        background-color: var(--bg);
        color: var(--text);
        transition:
          background-color 0.35s ease,
          color 0.35s ease;
      }

      [data-lang="bn"] body,
      body {
      }
      [data-lang="bn"] .lang-en {
        display: none !important;
      }
      [data-lang="en"] .lang-bn {
        display: none !important;
      }

      h1,
      h2,
      h3,
      h4,
      h5,
      h6 {
        font-family: "Sora", "Hind Siliguri", sans-serif;
        color: var(--text);
      }

      /* ── LANGUAGE FONT ── */
      [data-lang="bn"] * {
        font-family: "Hind Siliguri", "DM Sans", sans-serif;
      }
      [data-lang="bn"] h1,
      [data-lang="bn"] h2,
      [data-lang="bn"] h3,
      [data-lang="bn"] h4,
      [data-lang="bn"] h5,
      [data-lang="bn"] h6 {
        font-family: "Hind Siliguri", "Sora", sans-serif;
      }

      /* ── GLOBAL UTILITIES ── */
      .text-primary {
        color: var(--primary) !important;
      }
      .bg-primary {
        background-color: var(--primary) !important;
      }
      .text-muted {
        color: var(--text-muted) !important;
      }

      .btn-primary {
        background-color: var(--primary);
        border-color: var(--primary);
        color: #fff;
      }
      .btn-primary:hover {
        background-color: var(--primary-dark);
        border-color: var(--primary-dark);
      }
      .btn-outline-primary {
        color: var(--primary);
        border-color: var(--primary);
      }
      .btn-outline-primary:hover {
        background-color: var(--primary);
        color: #fff;
      }
      .btn-secondary {
        background-color: var(--secondary);
        border-color: var(--secondary);
        color: #fff;
      }

      /* ── NAVBAR ── */
      .navbar {
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.06);
        background: var(--navbar-bg) !important;
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        transition: background 0.35s;
      }
      .navbar-brand img {
        height: 40px;
      }
      .nav-link {
        font-weight: 500;
        color: var(--text) !important;
        font-family: "Sora", "Hind Siliguri", sans-serif;
        font-size: 0.87rem;
        transition: color 0.2s;
      }
      .nav-link:hover,
      .nav-link.active {
        color: var(--primary) !important;
      }
      .dropdown-menu {
        background: var(--bg-card);
        border: 1px solid var(--border);
        box-shadow: var(--shadow-md);
      }
      .dropdown-item {
        color: var(--text);
      }
      .dropdown-item:hover {
        background: var(--primary-light);
        color: var(--primary);
      }

      /* ── DARK MODE TOGGLE ── */
      .theme-toggle {
        width: 44px;
        height: 24px;
        background: var(--border);
        border-radius: 12px;
        position: relative;
        cursor: pointer;
        border: 2px solid var(--border);
        transition: background 0.3s;
        flex-shrink: 0;
      }
      [data-theme="dark"] .theme-toggle {
        background: var(--primary);
      }
      .theme-toggle::after {
        content: "";
        position: absolute;
        top: 1px;
        left: 2px;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: #fff;
        transition: transform 0.3s;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.2);
      }
      [data-theme="dark"] .theme-toggle::after {
        transform: translateX(20px);
      }
      .theme-toggle-wrap {
        display: flex;
        align-items: center;
        gap: 7px;
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--text-muted);
      }

      /* ── LANG TOGGLE ── */
      .lang-btn {
        background: var(--primary-light);
        border: 1.5px solid var(--primary);
        color: var(--primary);
        border-radius: 20px;
        padding: 4px 14px;
        font-size: 0.82rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
        letter-spacing: 0.5px;
      }
      .lang-btn:hover {
        background: var(--primary);
        color: #fff;
      }

      /* ── HERO ── */
      .hero-section {
        background: var(--hero-bg);
        padding: 130px 0 90px;
        position: relative;
        overflow: hidden;
      }
      .hero-section::before {
        content: "";
        position: absolute;
        top: -80px;
        right: -80px;
        width: 420px;
        height: 420px;
        background: rgba(25, 135, 84, 0.07);
        border-radius: 50%;
      }
      .hero-section::after {
        content: "";
        position: absolute;
        bottom: -60px;
        left: -100px;
        width: 300px;
        height: 300px;
        background: rgba(32, 201, 151, 0.06);
        border-radius: 50%;
      }
      .hero-title {
        font-size: 2.85rem;
        font-weight: 800;
        color: var(--text);
        line-height: 1.18;
      }
      .hero-subtitle {
        font-size: 1.08rem;
        color: var(--text-soft);
        max-width: 580px;
        line-height: 1.75;
      }
      .hero-img {
        border-radius: 20px;
        box-shadow: var(--shadow-lg);
      }
      .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(25, 135, 84, 0.12);
        border: 1px solid rgba(25, 135, 84, 0.25);
        color: var(--primary);
        padding: 6px 16px;
        border-radius: 30px;
        font-size: 0.82rem;
        font-weight: 600;
        margin-bottom: 14px;
      }

      /* ── FLOATING STAT BADGES (Hero) ── */
      .hero-stat {
        text-align: center;
      }
      .hero-stat .num {
        font-family: "Sora", sans-serif;
        font-size: 1.3rem;
        font-weight: 800;
        color: var(--primary);
      }
      .hero-stat .lbl {
        font-size: 0.78rem;
        color: var(--text-muted);
      }

      /* ── MARQUEE TRUST BAR ── */
      .trust-bar {
        background: var(--bg-alt);
        border-top: 1px solid var(--border);
        border-bottom: 1px solid var(--border);
        padding: 14px 0;
        overflow: hidden;
      }
      .trust-bar-track {
        display: flex;
        gap: 50px;
        animation: marquee 28s linear infinite;
        width: max-content;
      }
      .trust-bar-track span {
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--text-muted);
        white-space: nowrap;
        display: flex;
        align-items: center;
        gap: 7px;
      }
      .trust-bar-track span i {
        color: var(--primary);
      }
      @keyframes marquee {
        from {
          transform: translateX(0);
        }
        to {
          transform: translateX(-50%);
        }
      }

      /* ── STATS ── */
      .stats-section {
        background: linear-gradient(135deg, #0d3b1e 0%, #198754 100%);
        padding: 70px 0;
        position: relative;
        overflow: hidden;
      }
      .stats-section::before {
        content: "";
        position: absolute;
        top: -100px;
        right: -100px;
        width: 350px;
        height: 350px;
        background: rgba(255, 255, 255, 0.04);
        border-radius: 50%;
      }
      .stat-card {
        background: rgba(255, 255, 255, 0.12);
        border-radius: 18px;
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.12);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        padding: 26px 20px;
        transition: transform 0.3s ease;
        height: 100%;
      }
      .stat-card:hover {
        transform: translateY(-6px);
      }
      .stat-card .stat-icon {
        font-size: 2rem;
        color: #fff;
        margin-bottom: 12px;
        display: inline-block;
        background: rgba(255, 255, 255, 0.15);
        padding: 10px;
        border-radius: 12px;
      }
      .stat-number {
        font-size: 2.2rem;
        font-weight: 800;
        color: #fff;
        font-family: "Sora", sans-serif;
      }
      .stat-label {
        font-size: 0.88rem;
        color: rgba(255, 255, 255, 0.72);
        font-weight: 500;
      }
      .stat-card.stat-highlight {
        background: rgba(255, 255, 255, 0.22);
        border: 1px solid rgba(255, 255, 255, 0.38);
      }

      /* ── GLASS CARD ── */
      .glass-card {
        background: var(--bg-card2);
        border-radius: 16px;
        box-shadow: var(--shadow);
        backdrop-filter: blur(6px);
        border: 1px solid var(--border);
        transition:
          transform 0.3s,
          box-shadow 0.3s;
      }
      .glass-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-md);
      }

      /* ── FEATURES ── */
      .feature-icon {
        font-size: 1.8rem;
        color: var(--primary);
        background: rgba(25, 135, 84, 0.09);
        width: 62px;
        height: 62px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 16px;
        margin-bottom: 18px;
        transition: transform 0.3s;
      }
      .feature-card {
        border: 1px solid var(--border) !important;
        border-radius: 18px !important;
        background: var(--bg-card) !important;
        box-shadow: var(--shadow);
        transition: all 0.3s;
        /* height: 100%; */
      }
      .feature-card:hover {
        box-shadow: 0 14px 42px rgba(25, 135, 84, 0.14);
        transform: translateY(-5px);
        border-color: var(--primary) !important;
      }
      .feature-card:hover .feature-icon {
        transform: scale(1.1);
      }
      .feature-card h5 {
        color: var(--text);
      }
      .feature-card p {
        color: var(--text-muted);
      }

      /* ── HOW IT WORKS ── */
      .how-section {
        background: var(--bg-alt);
      }
      .step-card {
        background: var(--bg-card);
        border-radius: 18px;
        box-shadow: var(--shadow);
        border: 1px solid var(--border);
        padding: 2rem;
        transition: all 0.3s;
      }
      .step-card:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-4px);
      }
      .step-number {
        font-size: 3rem;
        font-weight: 800;
        color: var(--primary);
        font-family: "Sora", sans-serif;
        line-height: 1;
      }
      .step-card h5,
      .step-card p {
        color: var(--text);
      }
      .step-card p {
        color: var(--text-muted);
      }

      /* ── MODULES ── */
      .module-section {
        background: var(--bg-alt);
      }
      .module-img {
        border-radius: 18px;
        box-shadow: var(--shadow-md);
      }
      .module-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(25, 135, 84, 0.1);
        color: var(--primary);
        padding: 6px 14px;
        border-radius: 30px;
        font-size: 0.82rem;
        font-weight: 600;
        margin-bottom: 12px;
      }

      /* ── TESTIMONIALS ── */
      .testimonial-section {
        background: #0d3b1e;
      }
      [data-theme="dark"] .testimonial-section {
        background: #061610;
      }
      .testimonial-card {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 18px;
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.15);
        backdrop-filter: blur(6px);
        border: 1px solid rgba(255, 255, 255, 0.14);
        padding: 2rem;
        transition: all 0.3s;
      }
      .testimonial-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 16px 40px rgba(0, 0, 0, 0.22);
      }
      .testimonial-avatar {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid rgba(255, 255, 255, 0.3);
      }
      .stars {
        color: #ffc107;
      }

      /* ── SECURITY ── */
      .security-section {
        background: linear-gradient(135deg, #0d3b1e 0%, #1a5c34 100%);
      }
      [data-theme="dark"] .security-section {
        background: linear-gradient(135deg, #061610 0%, #0d2b19 100%);
      }
      .security-card {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 18px;
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.15);
        padding: 2rem 1.5rem;
        text-align: center;
        transition: all 0.3s;
      }
      .security-card:hover {
        transform: translateY(-5px);
        background: rgba(255, 255, 255, 0.16);
      }

      /* ── AWARDS SECTION ── */
      .awards-section {
        background: var(--bg);
      }
      .award-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 18px;
        padding: 1.8rem 1.5rem;
        text-align: center;
        box-shadow: var(--shadow);
        transition: all 0.3s;
      }
      .award-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-md);
        border-color: var(--primary);
      }
      .award-icon {
        font-size: 2.5rem;
        color: #ffc107;
        margin-bottom: 12px;
      }
      .award-card h6 {
        color: var(--text);
        font-weight: 700;
      }
      .award-card p {
        color: var(--text-muted);
        font-size: 0.85rem;
      }

      /* ── INTEGRATION SECTION ── */
      .integration-section {
        background: var(--bg-alt);
      }
      .integration-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: var(--bg-card);
        border: 1.5px solid var(--border);
        border-radius: 40px;
        padding: 8px 18px;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text);
        box-shadow: var(--shadow);
        transition: all 0.25s;
      }
      .integration-pill:hover {
        border-color: var(--primary);
        color: var(--primary);
        transform: scale(1.05);
      }
      .integration-pill i {
        color: var(--primary);
        font-size: 1.1rem;
      }

      /* ── ROADMAP SECTION ── */
      .roadmap-section {
        background: var(--bg);
      }
      .roadmap-item {
        display: flex;
        gap: 20px;
        align-items: flex-start;
        padding: 20px;
        border-radius: 16px;
        border: 1px solid var(--border);
        background: var(--bg-card);
        box-shadow: var(--shadow);
        margin-bottom: 16px;
        transition: all 0.3s;
      }
      .roadmap-item:hover {
        border-color: var(--primary);
        transform: translateX(6px);
      }
      .roadmap-dot {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: var(--primary);
        flex-shrink: 0;
        margin-top: 5px;
      }
      .roadmap-dot.done {
        background: var(--accent);
      }
      .roadmap-dot.upcoming {
        background: var(--accent2);
      }
      .roadmap-item h6 {
        color: var(--text);
        margin-bottom: 4px;
        font-weight: 700;
      }
      .roadmap-item p {
        color: var(--text-muted);
        font-size: 0.87rem;
        margin: 0;
      }

      /* ── CONTACT SECTION ── */
      .contact-section {
        background: var(--bg-alt);
      }
      .contact-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 20px;
        padding: 2.5rem;
        box-shadow: var(--shadow-md);
      }
      .contact-info-item {
        display: flex;
        gap: 14px;
        align-items: center;
        padding: 14px 0;
        border-bottom: 1px solid var(--border);
      }
      .contact-info-item:last-child {
        border-bottom: none;
      }
      .contact-info-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: rgba(25, 135, 84, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        color: var(--primary);
        flex-shrink: 0;
      }
      .contact-info-item h6 {
        color: var(--text);
        margin-bottom: 2px;
        font-size: 0.85rem;
      }
      .contact-info-item p {
        color: var(--text-muted);
        margin: 0;
        font-size: 0.9rem;
      }
      .form-control,
      .form-select {
        background: var(--bg-alt);
        border-color: var(--border);
        color: var(--text);
        border-radius: 10px;
      }
      .form-control:focus,
      .form-select:focus {
        background: var(--bg-alt);
        border-color: var(--primary);
        color: var(--text);
        box-shadow: 0 0 0 3px rgba(25, 135, 84, 0.15);
      }
      .form-label {
        color: var(--text-soft);
        font-size: 0.88rem;
        font-weight: 600;
      }
      .form-control::placeholder {
        color: var(--text-muted);
      }

      /* ── FAQ ── */
      .faq-section {
        background: var(--bg);
      }
      .accordion-item {
        background: var(--bg-card) !important;
        border: 1px solid var(--border) !important;
        border-radius: 14px !important;
        margin-bottom: 10px;
        overflow: hidden;
      }
      .accordion-button {
        background: var(--bg-card) !important;
        color: var(--text) !important;
        font-weight: 600;
        border-radius: 14px !important;
      }
      .accordion-button:not(.collapsed) {
        background: rgba(25, 135, 84, 0.09) !important;
        color: var(--primary) !important;
      }
      .accordion-button:focus {
        box-shadow: none;
      }
      .accordion-body {
        color: var(--text-muted);
        background: var(--bg-card);
      }
      .accordion-button::after {
        filter: var(--accordion-arrow);
      }
      [data-theme="dark"] {
        --accordion-arrow: invert(1);
      }

      /* ── CTA ── */
      .cta-section {
        background: linear-gradient(
          135deg,
          #0d3b1e 0%,
          #198754 60%,
          #1e7e5a 100%
        );
        position: relative;
        overflow: hidden;
      }
      .cta-section::before {
        content: "";
        position: absolute;
        bottom: -80px;
        left: -80px;
        width: 300px;
        height: 300px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 50%;
      }

      /* ── MOBILE APP ── */
      .mobile-section {
        background: linear-gradient(135deg, var(--bg-alt), var(--bg));
      }

      /* ── FOOTER ── */
      footer {
        background-color: var(--footer-bg);
        color: var(--footer-text);
        transition: background 0.35s;
      }
      footer a {
        color: #b8d9c8;
        text-decoration: none;
      }
      footer a:hover {
        color: #fff;
      }

      /* ── SCROLL-TO-TOP ── */
      .scroll-top {
        position: fixed;
        bottom: 28px;
        right: 28px;
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: var(--primary);
        color: #fff;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        box-shadow: var(--shadow-md);
        opacity: 0;
        transform: translateY(20px);
        transition: all 0.3s;
        z-index: 999;
      }
      .scroll-top.visible {
        opacity: 1;
        transform: translateY(0);
      }
      .scroll-top:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
      }

      /* ── SECTION BADGE ── */
      .sec-badge {
        display: inline-block;
        background: rgba(25, 135, 84, 0.12);
        color: var(--primary);
        padding: 5px 16px;
        border-radius: 30px;
        font-size: 0.82rem;
        font-weight: 700;
        margin-bottom: 12px;
        letter-spacing: 0.4px;
      }
      .sec-badge-light {
        background: rgba(255, 255, 255, 0.15);
        color: #fff;
      }

      [data-theme="dark"] .navbar-toggler {
        background: var(--primary);
      }
      [data-theme="dark"] .table>:not(caption)>*>* {
        color: #fff;
      }
      [data-theme="dark"] .table th, [data-theme="dark"] .table td {
        background: rgba(25, 135, 84, 0.08);
        border: 1px solid var(--border);
      }
      [data-theme="dark"] .table>:not(caption)>*>* {
        color: #fff;
      }

      /* ── ANIMATIONS ── */
      @keyframes fadeInUp {
        from {
          opacity: 0;
          transform: translateY(28px);
        }
        to {
          opacity: 1;
          transform: translateY(0);
        }
      }
      .fade-up {
        animation: fadeInUp 0.65s ease forwards;
      }
      .delay-1 {
        animation-delay: 0.1s;
      }
      .delay-2 {
        animation-delay: 0.22s;
      }
      .delay-3 {
        animation-delay: 0.34s;
      }

      @keyframes float {
        0%,
        100% {
          transform: translateY(0);
        }
        50% {
          transform: translateY(-8px);
        }
      }
      .float-anim {
        animation: float 4s ease-in-out infinite;
      }

      /* ── DARK BADGE FIX ── */
      .badge.bg-primary {
        background-color: var(--primary) !important;
      }

      @media (max-width: 768px) {
        .hero-title {
          font-size: 2rem;
        }
        .stat-number {
          font-size: 1.7rem;
        }
      }
    </style>

    @stack('styles')
    @livewireStyles
  </head>
  <body>
    <!-- ===== NAVBAR ===== -->
    @include('layouts.frontend.header')

      {{ $slot }}

    <!-- ===== FOOTER ===== -->
    @include('layouts.frontend.footer')

    <!-- Scroll to Top -->
    <button class="scroll-top" id="scrollTop" aria-label="Scroll to top">
      <i class="bi bi-arrow-up"></i>
    </button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
      // ── LANGUAGE TOGGLE ──
      const html = document.documentElement;
      const langBtn = document.getElementById("langToggle");

      // Saved language অথবা default
      let currentLang = localStorage.getItem("language") || "bn";

      setLanguage(currentLang);

      langBtn.addEventListener("click", () => {
          currentLang = currentLang === "bn" ? "en" : "bn";

          localStorage.setItem("language", currentLang);

          setLanguage(currentLang);
      });

      function setLanguage(lang) {
          html.setAttribute("data-lang", lang);
          html.setAttribute("lang", lang);
          langBtn.textContent = lang === "bn" ? "EN" : "বাং";
      }

      // ── DARK MODE TOGGLE ──
      const themeToggle = document.getElementById("themeToggle");
      const savedTheme = localStorage.getItem("govEduTheme") || "light";
      html.setAttribute("data-theme", savedTheme);

      themeToggle.addEventListener("click", () => {
        const current = html.getAttribute("data-theme");
        const next = current === "light" ? "dark" : "light";
        html.setAttribute("data-theme", next);
        localStorage.setItem("govEduTheme", next);
      });

      // ── SCROLL TO TOP ──
      const scrollTopBtn = document.getElementById("scrollTop");
      window.addEventListener("scroll", () => {
        scrollTopBtn.classList.toggle("visible", window.scrollY > 300);
      });
      scrollTopBtn.addEventListener("click", () =>
        window.scrollTo({ top: 0, behavior: "smooth" }),
      );

      // ── COUNTER ANIMATION ──
      function animateCounters() {
        document.querySelectorAll(".stat-number[data-count]").forEach((el) => {
          const target = parseInt(el.getAttribute("data-count"));
          const duration = 2000;
          const step = target / (duration / 16);
          let current = 0;
          const timer = setInterval(() => {
            current = Math.min(current + step, target);
            el.textContent = Math.floor(current).toLocaleString();
            if (current >= target) clearInterval(timer);
          }, 16);
        });
      }

      // Trigger counter when stats section is visible
      const statsSection = document.getElementById("stats-section");
      const observer = new IntersectionObserver(
        (entries) => {
          if (entries[0].isIntersecting) {
            animateCounters();
            observer.disconnect();
          }
        },
        { threshold: 0.3 },
      );
      if (statsSection) observer.observe(statsSection);

      // ── ACTIVE NAV ON SCROLL ──
      const sections = document.querySelectorAll("section[id]");
      window.addEventListener("scroll", () => {
        let current = "";
        sections.forEach((sec) => {
          if (window.scrollY >= sec.offsetTop - 120) current = sec.id;
        });
        document.querySelectorAll(".nav-link").forEach((link) => {
          link.classList.toggle(
            "active",
            link.getAttribute("href") === "#" + current,
          );
        });
      });
    </script>

    @stack('scripts')
    @livewireScripts
  </body>
</html>