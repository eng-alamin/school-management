<!doctype html>
<html lang="bn" data-theme="light" data-lang="bn">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>EMS – জাতীয় বিদ্যালয় ব্যবস্থাপনা সিস্টেম</title>
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
      /* ══════════════════════════════════════════
       DESIGN TOKENS – LIGHT & DARK
    ══════════════════════════════════════════ */
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
        height: 100%;
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
    <nav class="navbar navbar-expand-lg fixed-top py-3">
      <div class="container">
        <a class="navbar-brand" href="#">
          <img
            src="https://placehold.co/150x40/198754/ffffff?text=EMS"
            alt="GovEdu Logo"
            class="rounded"
          />
        </a>
        <button
          class="navbar-toggler"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#navbarMain"
        >
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarMain">
          <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-1">
            <li class="nav-item">
              <a class="nav-link active" href="#">
                <span class="lang-bn">হোম</span
                ><span class="lang-en">Home</span>
              </a>
            </li>
            <li class="nav-item dropdown">
              <a
                class="nav-link dropdown-toggle"
                href="#"
                role="button"
                data-bs-toggle="dropdown"
              >
                <span class="lang-bn">ফিচার</span
                ><span class="lang-en">Features</span>
              </a>
              <ul class="dropdown-menu">
                <li>
                  <a class="dropdown-item" href="#features-section">
                    <span class="lang-bn">ড্যাশবোর্ড</span
                    ><span class="lang-en">Dashboard</span>
                  </a>
                </li>
                <li>
                  <a class="dropdown-item" href="#">
                    <span class="lang-bn">যোগাযোগ</span
                    ><span class="lang-en">Communication</span>
                  </a>
                </li>
                <li>
                  <a class="dropdown-item" href="#">
                    <span class="lang-bn">বিশ্লেষণ</span
                    ><span class="lang-en">Analytics</span>
                  </a>
                </li>
              </ul>
            </li>
            <li class="nav-item dropdown">
              <a
                class="nav-link dropdown-toggle"
                href="#"
                data-bs-toggle="dropdown"
              >
                <span class="lang-bn">মডিউল</span
                ><span class="lang-en">Modules</span>
              </a>
              <ul class="dropdown-menu">
                <li>
                  <a class="dropdown-item" href="#modules-section">
                    <span class="lang-bn">শিক্ষার্থী ব্যবস্থাপনা</span
                    ><span class="lang-en">Student Management</span>
                  </a>
                </li>
                <li>
                  <a class="dropdown-item" href="#modules-section">
                    <span class="lang-bn">শিক্ষক ও কর্মচারী</span
                    ><span class="lang-en">Teacher &amp; Staff</span>
                  </a>
                </li>
                <li>
                  <a class="dropdown-item" href="#modules-section">
                    <span class="lang-bn">উপস্থিতি</span
                    ><span class="lang-en">Attendance</span>
                  </a>
                </li>
                <li>
                  <a class="dropdown-item" href="#modules-section">
                    <span class="lang-bn">পরীক্ষা</span
                    ><span class="lang-en">Examinations</span>
                  </a>
                </li>
              </ul>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#security-section">
                <span class="lang-bn">নিরাপত্তা</span
                ><span class="lang-en">Security</span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#contact-section">
                <span class="lang-bn">যোগাযোগ</span
                ><span class="lang-en">Contact</span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#faq-section">FAQ</a>
            </li>
          </ul>
          <div class="d-flex align-items-center gap-3 flex-wrap">
            <!-- Language Toggle -->
            <button class="lang-btn" id="langToggle">EN</button>
            <!-- Dark Mode Toggle -->
            <div class="theme-toggle-wrap">
              <i
                class="bi bi-sun-fill"
                style="color: var(--accent2); font-size: 0.95rem"
              ></i>
              <div
                class="theme-toggle"
                id="themeToggle"
                role="button"
                aria-label="Toggle dark mode"
              ></div>
              <i
                class="bi bi-moon-fill"
                style="color: #8ca9b5; font-size: 0.85rem"
              ></i>
            </div>
           @auth
                <a href="{{ route('dashboard') }}" class="btn btn-success btn-sm">
                    <span class="lang-bn">ড্যাশবোর্ড</span>
                    <span class="lang-en">Dashboard</span>
                </a>
            @else
                <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm">
                    <span class="lang-bn">লগইন</span>
                    <span class="lang-en">Login</span>
                </a>
                <a href="{{route('register')}}" class="btn btn-primary btn-sm">
                  <span class="lang-bn">বিনামূল্যে নিবন্ধন</span>
                  <span class="lang-en">Register Free</span>
                </a>
            @endauth
          </div>
        </div>
      </div>
    </nav>

    <!-- ===== HERO ===== -->
    <section class="hero-section">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-lg-6">
            <div class="hero-badge fade-up">
              🇧🇩
              <span class="lang-bn">জাতীয় শিক্ষা প্ল্যাটফর্ম</span>
              <span class="lang-en">National Education Platform</span>
            </div>
            <h1 class="hero-title fade-up">
              <span class="lang-bn"
                >একটি ডিজিটাল ইকোসিস্টেমে জাতীয় শিক্ষার ক্ষমতায়ন</span
              >
              <span class="lang-en"
                >Empowering National Education with a Unified SaaS
                Platform</span
              >
            </h1>
            <p class="hero-subtitle mt-3 fade-up delay-1">
              <span class="lang-bn"
                >সকল স্কুল, কলেজ ও মাদ্রাসা একটি ডিজিটাল ইকোসিস্টেমে পরিচালনা
                করুন। সরকারের জন্য নির্মিত, হাজারো প্রতিষ্ঠানের
                বিশ্বাসভাজন।</span
              >
              <span class="lang-en"
                >Manage all schools, colleges, and madrasas under one digital
                ecosystem. Built for the government, trusted by thousands.</span
              >
            </p>
            <div class="mt-4 d-flex gap-3 flex-wrap fade-up delay-2">
              <a href="#" class="btn btn-primary btn-lg px-4 rounded-pill">
                <span class="lang-bn">বিনামূল্যে শুরু করুন</span>
                <span class="lang-en">Get Started Free</span>
              </a>
              <a
                href="#"
                class="btn btn-outline-primary btn-lg px-4 rounded-pill"
              >
                <i class="bi bi-play-circle me-1"></i>
                <span class="lang-bn">ডেমো দেখুন</span>
                <span class="lang-en">Watch Demo</span>
              </a>
            </div>
            <div class="mt-4 d-flex gap-4 flex-wrap fade-up delay-3">
              <div class="hero-stat">
                <div class="num">23K+</div>
                <div class="lbl">
                  <span class="lang-bn">বিদ্যালয়</span
                  ><span class="lang-en">Schools</span>
                </div>
              </div>
              <div class="hero-stat">
                <div class="num">5M+</div>
                <div class="lbl">
                  <span class="lang-bn">শিক্ষার্থী</span
                  ><span class="lang-en">Students</span>
                </div>
              </div>
              <div class="hero-stat">
                <div class="num">99.9%</div>
                <div class="lbl">
                  <span class="lang-bn">আপটাইম</span
                  ><span class="lang-en">Uptime</span>
                </div>
              </div>
              <div class="hero-stat">
                <div class="num">64</div>
                <div class="lbl">
                  <span class="lang-bn">জেলা</span
                  ><span class="lang-en">Districts</span>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-6 mt-5 mt-lg-0 text-center">
            <img
              src="https://images.unsplash.com/photo-1531482615713-2afd69097998?w=700&auto=format&fit=crop&q=80"
              alt="School Dashboard"
              class="img-fluid hero-img float-anim"
            />
          </div>
        </div>
      </div>
    </section>

    <!-- ===== TRUST BAR ===== -->
    <div class="trust-bar">
      <div class="trust-bar-track" id="trustTrack">
        <span
          ><i class="bi bi-patch-check-fill"></i>
          <span class="lang-bn">শিক্ষা মন্ত্রণালয় অনুমোদিত</span
          ><span class="lang-en">Ministry of Education Approved</span></span
        >
        <span
          ><i class="bi bi-shield-check"></i>
          <span class="lang-bn">ISO 27001 সার্টিফাইড</span
          ><span class="lang-en">ISO 27001 Certified</span></span
        >
        <span
          ><i class="bi bi-building-check"></i>
          <span class="lang-bn">DSHE ইন্টিগ্রেটেড</span
          ><span class="lang-en">DSHE Integrated</span></span
        >
        <span
          ><i class="bi bi-award-fill"></i>
          <span class="lang-bn">জাতীয় ই-গভ পুরস্কার ২০২৪</span
          ><span class="lang-en">National e-Gov Award 2024</span></span
        >
        <span
          ><i class="bi bi-globe2"></i>
          <span class="lang-bn">a2i ডিজিটাল বাংলাদেশ পার্টনার</span
          ><span class="lang-en">a2i Digital Bangladesh Partner</span></span
        >
        <span
          ><i class="bi bi-people-fill"></i>
          <span class="lang-bn">৫০ লক্ষ+ শিক্ষার্থী সংযুক্ত</span
          ><span class="lang-en">5M+ Students Connected</span></span
        >
        <!-- duplicate for loop -->
        <span
          ><i class="bi bi-patch-check-fill"></i>
          <span class="lang-bn">শিক্ষা মন্ত্রণালয় অনুমোদিত</span
          ><span class="lang-en">Ministry of Education Approved</span></span
        >
        <span
          ><i class="bi bi-shield-check"></i>
          <span class="lang-bn">ISO 27001 সার্টিফাইড</span
          ><span class="lang-en">ISO 27001 Certified</span></span
        >
        <span
          ><i class="bi bi-building-check"></i>
          <span class="lang-bn">DSHE ইন্টিগ্রেটেড</span
          ><span class="lang-en">DSHE Integrated</span></span
        >
        <span
          ><i class="bi bi-award-fill"></i>
          <span class="lang-bn">জাতীয় ই-গভ পুরস্কার ২০২৪</span
          ><span class="lang-en">National e-Gov Award 2024</span></span
        >
        <span
          ><i class="bi bi-globe2"></i>
          <span class="lang-bn">a2i ডিজিটাল বাংলাদেশ পার্টনার</span
          ><span class="lang-en">a2i Digital Bangladesh Partner</span></span
        >
        <span
          ><i class="bi bi-people-fill"></i>
          <span class="lang-bn">৫০ লক্ষ+ শিক্ষার্থী সংযুক্ত</span
          ><span class="lang-en">5M+ Students Connected</span></span
        >
      </div>
    </div>

    <!-- ===== STATISTICS ===== -->
    <section class="stats-section" id="stats-section">
      <div class="container">
        <div class="text-center mb-5">
          <h2 class="fw-bold text-white">
            <span class="lang-bn">আমাদের জাতীয় পদচিহ্ন</span>
            <span class="lang-en">Our Nationwide Footprint</span>
          </h2>
          <p style="color: rgba(255, 255, 255, 0.65)">
            <span class="lang-bn"
              >কেন্দ্রীয় শিক্ষা ব্যবস্থাপনা সিস্টেম থেকে রিয়েল-টাইম ডেটা</span
            >
            <span class="lang-en"
              >Real-time data from the central education management system</span
            >
          </p>
        </div>
        <div class="row g-4">
          <div class="col-lg-2 col-md-4 col-6">
            <div class="stat-card text-center">
              <div class="stat-icon"><i class="bi bi-building"></i></div>
              <div class="stat-number" data-count="19028">0</div>
              <div class="stat-label">
                <span class="lang-bn">উচ্চ বিদ্যালয়</span
                ><span class="lang-en">High Schools</span>
              </div>
            </div>
          </div>
          <div class="col-lg-2 col-md-4 col-6">
            <div class="stat-card text-center">
              <div class="stat-icon"><i class="bi bi-bank"></i></div>
              <div class="stat-number" data-count="3045">0</div>
              <div class="stat-label">
                <span class="lang-bn">কলেজ</span
                ><span class="lang-en">Colleges</span>
              </div>
            </div>
          </div>
          <div class="col-lg-2 col-md-4 col-6">
            <div class="stat-card text-center">
              <div class="stat-icon"><i class="bi bi-house-door"></i></div>
              <div class="stat-number" data-count="1196">0</div>
              <div class="stat-label">
                <span class="lang-bn">স্কুল ও কলেজ</span
                ><span class="lang-en">School &amp; College</span>
              </div>
            </div>
          </div>
          <div class="col-lg-2 col-md-4 col-6">
            <div class="stat-card text-center">
              <div class="stat-icon"><i class="bi bi-person-check"></i></div>
              <div class="stat-number" data-count="136316">0</div>
              <div class="stat-label">
                <span class="lang-bn">কর্মচারী</span
                ><span class="lang-en">Staff</span>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-8 col-12">
            <div
              class="stat-card stat-highlight text-center text-md-start d-flex flex-column flex-md-row align-items-center justify-content-between"
            >
              <div>
                <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
                <div class="stat-number" data-count="407141">0</div>
                <div class="stat-label">
                  <span class="lang-bn">সারাদেশে শিক্ষক</span
                  ><span class="lang-en">Teachers Nationwide</span>
                </div>
              </div>
              <div class="mt-3 mt-md-0">
                <span class="badge bg-light text-dark p-2"
                  ><i class="bi bi-arrow-up-right"></i> +2.5%</span
                >
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== FEATURES ===== -->
    <section
      class="py-5 py-lg-6"
      id="features-section"
      style="background: var(--bg)"
    >
      <div class="container">
        <div class="text-center mb-5">
          <span class="sec-badge">
            <span class="lang-bn">মূল ফিচার</span
            ><span class="lang-en">Core Features</span>
          </span>
          <h2 class="fw-bold">
            <span class="lang-bn">সব-ইন-ওয়ান বিদ্যালয় ব্যবস্থাপনা</span>
            <span class="lang-en">All-in-One School Management</span>
          </h2>
          <p class="text-muted">
            <span class="lang-bn"
              >একটি আধুনিক শিক্ষা প্রতিষ্ঠান পরিচালনার জন্য সবকিছু</span
            >
            <span class="lang-en"
              >Everything you need to run a modern educational institution</span
            >
          </p>
        </div>
        <div class="row g-4">
          <div class="col-md-4">
            <div class="card feature-card p-4">
              <div class="feature-icon"><i class="bi bi-people"></i></div>
              <h5>
                <span class="lang-bn">শিক্ষার্থী ও অভিভাবক পোর্টাল</span
                ><span class="lang-en">Student &amp; Parent Portal</span>
              </h5>
              <p class="text-muted mb-0">
                <span class="lang-bn"
                  >ভর্তি, প্রোফাইল, ডকুমেন্ট এবং রিয়েল-টাইম অগ্রগতি ট্র্যাকিং।
                  অভিভাবকরা ২৪/৭ অবহিত থাকেন।</span
                ><span class="lang-en"
                  >Admission, profiles, documents, and real-time progress
                  tracking. Parents stay informed 24/7.</span
                >
              </p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card feature-card p-4">
              <div class="feature-icon">
                <i class="bi bi-calendar-check"></i>
              </div>
              <h5>
                <span class="lang-bn">স্মার্ট উপস্থিতি</span
                ><span class="lang-en">Smart Attendance</span>
              </h5>
              <p class="text-muted mb-0">
                <span class="lang-bn"
                  >বায়োমেট্রিক, RFID ও GPS-সক্ষম উপস্থিতি ট্র্যাকিং, তাৎক্ষণিক
                  SMS/ইমেইল সতর্কতা সহ।</span
                ><span class="lang-en"
                  >Biometric, RFID, and GPS-enabled attendance with instant
                  SMS/email alerts to guardians.</span
                >
              </p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card feature-card p-4">
              <div class="feature-icon">
                <i class="bi bi-file-earmark-text"></i>
              </div>
              <h5>
                <span class="lang-bn">পরীক্ষা ও ফলাফল</span
                ><span class="lang-en">Examination &amp; Results</span>
              </h5>
              <p class="text-muted mb-0">
                <span class="lang-bn"
                  >পরীক্ষা তৈরি, মার্কশিট, অটো-গ্রেডিং এবং বিশ্লেষণসহ অনলাইনে
                  ফলাফল প্রকাশ।</span
                ><span class="lang-en"
                  >Create exams, mark sheets, auto-grading, and publish results
                  online with analytics.</span
                >
              </p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card feature-card p-4">
              <div class="feature-icon"><i class="bi bi-cash-coin"></i></div>
              <h5>
                <span class="lang-bn">ফি ও অ্যাকাউন্টিং</span
                ><span class="lang-en">Fees &amp; Accounting</span>
              </h5>
              <p class="text-muted mb-0">
                <span class="lang-bn"
                  >অনলাইন ফি সংগ্রহ, ইনভয়েস, ব্যয় ট্র্যাকিং এবং ERP-এর সাথে
                  পূর্ণ অ্যাকাউন্টিং।</span
                ><span class="lang-en"
                  >Online fee collection, invoices, expense tracking, and full
                  accounting linked to ERP.</span
                >
              </p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card feature-card p-4">
              <div class="feature-icon"><i class="bi bi-bell"></i></div>
              <h5>
                <span class="lang-bn">নোটিফিকেশন হাব</span
                ><span class="lang-en">Notification Hub</span>
              </h5>
              <p class="text-muted mb-0">
                <span class="lang-bn"
                  >ঘোষণা, ইভেন্ট এবং জরুরি পরিস্থিতির জন্য পুশ, SMS, ইমেইল এবং
                  ইন-অ্যাপ সতর্কতা।</span
                ><span class="lang-en"
                  >Push notifications, SMS, email, and in-app alerts for
                  announcements, events, and emergencies.</span
                >
              </p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card feature-card p-4">
              <div class="feature-icon"><i class="bi bi-graph-up"></i></div>
              <h5>
                <span class="lang-bn">উন্নত রিপোর্ট</span
                ><span class="lang-en">Advanced Reports</span>
              </h5>
              <p class="text-muted mb-0">
                <span class="lang-bn"
                  >মন্ত্রণালয়ের কর্মকর্তা, পরিদর্শক এবং প্রধান শিক্ষকদের জন্য
                  কাস্টমাইজযোগ্য ড্যাশবোর্ড।</span
                ><span class="lang-en"
                  >Customizable dashboards for ministry officials, inspectors,
                  and school heads.</span
                >
              </p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card feature-card p-4">
              <div class="feature-icon"><i class="bi bi-translate"></i></div>
              <h5>
                <span class="lang-bn">দ্বিভাষিক ইন্টারফেস</span
                ><span class="lang-en">Bilingual Interface</span>
              </h5>
              <p class="text-muted mb-0">
                <span class="lang-bn"
                  >ইউনিকোড সমর্থনসহ পূর্ণ বাংলা ও ইংরেজি UI। সমস্ত মডিউল,
                  রিপোর্ট ও অ্যাপে।</span
                ><span class="lang-en"
                  >Full Bangla and English UI with Unicode support. Across all
                  modules, reports, and apps.</span
                >
              </p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card feature-card p-4">
              <div class="feature-icon"><i class="bi bi-bus-front"></i></div>
              <h5>
                <span class="lang-bn">পরিবহন ব্যবস্থাপনা</span
                ><span class="lang-en">Transport Management</span>
              </h5>
              <p class="text-muted mb-0">
                <span class="lang-bn"
                  >লাইভ GPS ট্র্যাকিং, রুট অপ্টিমাইজেশন এবং অভিভাবকদের জন্য
                  রিয়েল-টাইম বাস লোকেশন।</span
                ><span class="lang-en"
                  >Live GPS tracking, route optimization, and real-time bus
                  location for parents.</span
                >
              </p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card feature-card p-4">
              <div class="feature-icon"><i class="bi bi-book"></i></div>
              <h5>
                <span class="lang-bn">লাইব্রেরি সিস্টেম</span
                ><span class="lang-en">Library System</span>
              </h5>
              <p class="text-muted mb-0">
                <span class="lang-bn"
                  >ডিজিটাল ক্যাটালগ, বই ইস্যু/রিটার্ন ট্র্যাকিং, ফাইন
                  ম্যানেজমেন্ট এবং ই-বুক ইন্টিগ্রেশন।</span
                ><span class="lang-en"
                  >Digital catalog, book issue/return tracking, fine management,
                  and e-book integration.</span
                >
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== HOW IT WORKS ===== -->
    <section class="py-5 how-section">
      <div class="container text-center">
        <span class="sec-badge">
          <span class="lang-bn">সহজ প্রক্রিয়া</span
          ><span class="lang-en">Simple Process</span>
        </span>
        <h2 class="fw-bold">
          <span class="lang-bn">৩টি সহজ ধাপে শুরু করুন</span>
          <span class="lang-en">Get Started in 3 Simple Steps</span>
        </h2>
        <p class="text-muted mb-5">
          <span class="lang-bn">২৪ ঘণ্টার মধ্যে চালু এবং পরিচালনাযোগ্য</span>
          <span class="lang-en">Up and running in under 24 hours</span>
        </p>
        <div class="row mt-2 g-4">
          <div class="col-md-4">
            <div class="step-card h-100">
              <div class="step-number">০১</div>
              <h5 class="mt-3">
                <span class="lang-bn">প্রতিষ্ঠান নিবন্ধন করুন</span>
                <span class="lang-en">Register Your Institution</span>
              </h5>
              <p class="text-muted mb-0">
                <span class="lang-bn"
                  >আপনার অফিসিয়াল EMIS কোড দিয়ে সাইন আপ করুন এবং তাৎক্ষণিকভাবে
                  যাচাই করুন।</span
                >
                <span class="lang-en"
                  >Sign up with your official EMIS code and verify
                  instantly.</span
                >
              </p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="step-card h-100">
              <div class="step-number">০২</div>
              <h5 class="mt-3">
                <span class="lang-bn">মডিউল কনফিগার করুন</span>
                <span class="lang-en">Configure Modules</span>
              </h5>
              <p class="text-muted mb-0">
                <span class="lang-bn"
                  >উপস্থিতি থেকে অ্যাকাউন্ট পর্যন্ত আপনার প্রয়োজনীয় মডিউল বেছে
                  নিন।</span
                >
                <span class="lang-en"
                  >Choose the modules you need – from attendance to
                  accounts.</span
                >
              </p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="step-card h-100">
              <div class="step-number">০৩</div>
              <h5 class="mt-3">
                <span class="lang-bn">লঞ্চ করুন ও প্রশিক্ষণ দিন</span>
                <span class="lang-en">Launch &amp; Train Staff</span>
              </h5>
              <p class="text-muted mb-0">
                <span class="lang-bn"
                  >আমাদের গাইডেড ট্রেনিং ও ২৪/৭ সাপোর্ট দিয়ে সিস্টেম ব্যবহার
                  শুরু করুন।</span
                >
                <span class="lang-en"
                  >Start using the system with our guided training and 24/7
                  support.</span
                >
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== MODULE SHOWCASE ===== -->
    <section class="py-5 module-section" id="modules-section">
      <div class="container">
        <div class="text-center mb-5">
          <span class="sec-badge">
            <span class="lang-bn">মডিউল ইন অ্যাকশন</span
            ><span class="lang-en">Modules In Action</span>
          </span>
          <h2 class="fw-bold">
            <span class="lang-bn">প্রতিটি ভূমিকার জন্য শক্তিশালী টুল</span>
            <span class="lang-en">Powerful Tools for Every Role</span>
          </h2>
          <p class="text-muted">
            <span class="lang-bn"
              >শিক্ষক, প্রশাসক, অভিভাবক এবং মন্ত্রণালয় কর্মকর্তাদের জন্য
              নির্মিত</span
            >
            <span class="lang-en"
              >Built for teachers, admins, parents, and ministry officials</span
            >
          </p>
        </div>
        <!-- Module 1 -->
        <div class="row align-items-center g-5 mb-5">
          <div class="col-lg-6">
            <img
              src="https://images.unsplash.com/photo-1509062522246-3755977927d7?w=600&auto=format&fit=crop&q=80"
              alt="Student Management"
              class="img-fluid module-img w-100"
            />
          </div>
          <div class="col-lg-6">
            <div class="module-badge">
              <i class="bi bi-people-fill"></i>
              <span class="lang-bn">শিক্ষার্থী ব্যবস্থাপনা</span
              ><span class="lang-en">Student Management</span>
            </div>
            <h3 class="fw-bold">
              <span class="lang-bn"
                >সম্পূর্ণ শিক্ষার্থী জীবনচক্র ব্যবস্থাপনা</span
              >
              <span class="lang-en">Complete Student Lifecycle Management</span>
            </h3>
            <p class="text-muted">
              <span class="lang-bn"
                >ভর্তি থেকে স্নাতক পর্যন্ত — প্রতিটি শিক্ষার্থীর রেকর্ড,
                ডকুমেন্ট এবং একাডেমিক যাত্রা ডিজিটালভাবে পরিচালনা করুন।</span
              >
              <span class="lang-en"
                >From admission to graduation — manage every student record,
                document, and academic journey digitally.</span
              >
            </p>
            <ul class="list-unstyled mt-3">
              <li class="mb-2">
                <i class="bi bi-check-circle-fill text-primary me-2"></i
                ><span class="lang-bn">অনলাইন ভর্তি ও নথিভুক্তি</span
                ><span class="lang-en">Online admission &amp; enrollment</span>
              </li>
              <li class="mb-2">
                <i class="bi bi-check-circle-fill text-primary me-2"></i
                ><span class="lang-bn">ডিজিটাল শিক্ষার্থী আইডি কার্ড</span
                ><span class="lang-en">Digital student ID cards</span>
              </li>
              <li class="mb-2">
                <i class="bi bi-check-circle-fill text-primary me-2"></i
                ><span class="lang-bn">অভিভাবক মোবাইল অ্যাপ অ্যাক্সেস</span
                ><span class="lang-en">Parent mobile app access</span>
              </li>
              <li class="mb-2">
                <i class="bi bi-check-circle-fill text-primary me-2"></i
                ><span class="lang-bn">ট্রান্সফার সার্টিফিকেট অটোমেশন</span
                ><span class="lang-en">Transfer certificate automation</span>
              </li>
            </ul>
            <a href="#" class="btn btn-primary rounded-pill mt-2 px-4">
              <span class="lang-bn">মডিউল দেখুন</span
              ><span class="lang-en">Explore Module</span>
            </a>
          </div>
        </div>
        <!-- Module 2 -->
        <div class="row align-items-center g-5 mb-5 flex-lg-row-reverse">
          <div class="col-lg-6">
            <img
              src="https://images.unsplash.com/photo-1606761568499-6d2451b23c66?w=600&auto=format&fit=crop&q=80"
              alt="Teacher Management"
              class="img-fluid module-img w-100"
            />
          </div>
          <div class="col-lg-6">
            <div class="module-badge">
              <i class="bi bi-person-badge"></i>
              <span class="lang-bn">শিক্ষক ও কর্মচারী</span
              ><span class="lang-en">Teacher &amp; Staff</span>
            </div>
            <h3 class="fw-bold">
              <span class="lang-bn"
                >শিক্ষকদের জন্য স্ট্রিমলাইনড HR ও পেরোল</span
              >
              <span class="lang-en"
                >Streamlined HR &amp; Payroll for Educators</span
              >
            </h3>
            <p class="text-muted">
              <span class="lang-bn"
                >সম্পূর্ণ শিক্ষক প্রোফাইল, যোগ্যতা ট্র্যাকিং, ছুটি ব্যবস্থাপনা
                এবং স্বয়ংক্রিয় বেতন বিতরণ।</span
              >
              <span class="lang-en"
                >Full teacher profiles, qualification tracking, leave
                management, and automated salary disbursement.</span
              >
            </p>
            <ul class="list-unstyled mt-3">
              <li class="mb-2">
                <i class="bi bi-check-circle-fill text-primary me-2"></i
                ><span class="lang-bn">MPO ও নন-MPO ব্যবস্থাপনা</span
                ><span class="lang-en">MPO &amp; non-MPO management</span>
              </li>
              <li class="mb-2">
                <i class="bi bi-check-circle-fill text-primary me-2"></i
                ><span class="lang-bn">ছুটি ও বিকল্প সময়সূচি</span
                ><span class="lang-en">Leave &amp; substitute scheduling</span>
              </li>
              <li class="mb-2">
                <i class="bi bi-check-circle-fill text-primary me-2"></i
                ><span class="lang-bn">কর্মক্ষমতা মূল্যায়ন</span
                ><span class="lang-en">Performance evaluation</span>
              </li>
              <li class="mb-2">
                <i class="bi bi-check-circle-fill text-primary me-2"></i
                ><span class="lang-bn">পেরোল ও বেতন বৃদ্ধির ইতিহাস</span
                ><span class="lang-en">Payroll &amp; increment history</span>
              </li>
            </ul>
            <a href="#" class="btn btn-primary rounded-pill mt-2 px-4">
              <span class="lang-bn">মডিউল দেখুন</span
              ><span class="lang-en">Explore Module</span>
            </a>
          </div>
        </div>
        <!-- Module 3 -->
        <div class="row align-items-center g-5">
          <div class="col-lg-6">
            <img
              src="https://images.unsplash.com/photo-1553877522-43269d4ea984?w=600&auto=format&fit=crop&q=80"
              alt="Analytics"
              class="img-fluid module-img w-100"
            />
          </div>
          <div class="col-lg-6">
            <div class="module-badge">
              <i class="bi bi-graph-up-arrow"></i>
              <span class="lang-bn">বিশ্লেষণ ও রিপোর্টিং</span
              ><span class="lang-en">Analytics &amp; Reporting</span>
            </div>
            <h3 class="fw-bold">
              <span class="lang-bn">মন্ত্রণালয়-গ্রেড বিশ্লেষণ ড্যাশবোর্ড</span>
              <span class="lang-en">Ministry-Grade Analytics Dashboard</span>
            </h3>
            <p class="text-muted">
              <span class="lang-bn"
                >জাতীয় স্তর থেকে পৃথক শ্রেণিকক্ষ পর্যন্ত ড্রিল-ডাউন।
                মন্ত্রণালয়ের কর্মকর্তারা রিয়েল টাইমে সব প্রতিষ্ঠানের KPI
                পর্যবেক্ষণ করতে পারেন।</span
              >
              <span class="lang-en"
                >Drill-down from national level to individual classroom.
                Ministry officials can monitor KPIs in real time across all
                institutions.</span
              >
            </p>
            <ul class="list-unstyled mt-3">
              <li class="mb-2">
                <i class="bi bi-check-circle-fill text-primary me-2"></i
                ><span class="lang-bn">লাইভ উপস্থিতি হিটম্যাপ</span
                ><span class="lang-en">Live attendance heatmaps</span>
              </li>
              <li class="mb-2">
                <i class="bi bi-check-circle-fill text-primary me-2"></i
                ><span class="lang-bn">পরীক্ষার ফলাফল ট্রেন্ড বিশ্লেষণ</span
                ><span class="lang-en">Exam result trend analysis</span>
              </li>
              <li class="mb-2">
                <i class="bi bi-check-circle-fill text-primary me-2"></i
                ><span class="lang-bn">জেলা-স্তরের তুলনা</span
                ><span class="lang-en">District-level comparisons</span>
              </li>
              <li class="mb-2">
                <i class="bi bi-check-circle-fill text-primary me-2"></i
                ><span class="lang-bn">এক্সপোর্টযোগ্য মন্ত্রণালয় রিপোর্ট</span
                ><span class="lang-en">Exportable ministry reports</span>
              </li>
            </ul>
            <a href="#" class="btn btn-primary rounded-pill mt-2 px-4">
              <span class="lang-bn">মডিউল দেখুন</span
              ><span class="lang-en">Explore Module</span>
            </a>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== INTEGRATIONS ===== -->
    <section class="py-5 integration-section" id="integrations-section">
      <div class="container">
        <div class="text-center mb-5">
          <span class="sec-badge">
            <span class="lang-bn">ইন্টিগ্রেশন</span
            ><span class="lang-en">Integrations</span>
          </span>
          <h2 class="fw-bold">
            <span class="lang-bn">আপনার পছন্দের টুলের সাথে সংযুক্ত</span>
            <span class="lang-en">Connected with Your Favourite Tools</span>
          </h2>
          <p class="text-muted">
            <span class="lang-bn"
              >সরকারি সিস্টেম থেকে পেমেন্ট গেটওয়ে পর্যন্ত সবকিছুর সাথে
              নিরবচ্ছিন্ন ইন্টিগ্রেশন</span
            >
            <span class="lang-en"
              >Seamless integration with government systems, payment gateways,
              and more</span
            >
          </p>
        </div>
        <div class="d-flex flex-wrap gap-3 justify-content-center">
          <div class="integration-pill"><i class="bi bi-bank2"></i> bKash</div>
          <div class="integration-pill"><i class="bi bi-phone"></i> Nagad</div>
          <div class="integration-pill">
            <i class="bi bi-credit-card"></i> Dutch-Bangla Bank
          </div>
          <div class="integration-pill">
            <i class="bi bi-building-gear"></i> EMIS
          </div>
          <div class="integration-pill">
            <i class="bi bi-person-vcard"></i> NID API
          </div>
          <div class="integration-pill">
            <i class="bi bi-chat-dots"></i> SMS Gateway
          </div>
          <div class="integration-pill">
            <i class="bi bi-envelope-at"></i> Email SMTP
          </div>
          <div class="integration-pill">
            <i class="bi bi-google"></i> Google Workspace
          </div>
          <div class="integration-pill">
            <i class="bi bi-microsoft"></i> Microsoft 365
          </div>
          <div class="integration-pill">
            <i class="bi bi-whatsapp"></i> WhatsApp Business
          </div>
          <div class="integration-pill">
            <i class="bi bi-fingerprint"></i> Biometric Devices
          </div>
          <div class="integration-pill">
            <i class="bi bi-broadcast"></i> DSHE Portal
          </div>
        </div>
      </div>
    </section>

    <!-- ===== SECURITY ===== -->
    <section class="py-5 security-section" id="security-section">
      <div class="container">
        <div class="text-center mb-5">
          <span class="sec-badge sec-badge-light">
            <span class="lang-bn">এন্টারপ্রাইজ গ্রেড</span
            ><span class="lang-en">Enterprise Grade</span>
          </span>
          <h2 class="fw-bold text-white">
            <span class="lang-bn"
              >আপনি বিশ্বাস করতে পারেন এমন নিরাপত্তা ও সম্মতি</span
            >
            <span class="lang-en">Security &amp; Compliance You Can Trust</span>
          </h2>
          <p style="color: rgba(255, 255, 255, 0.65)">
            <span class="lang-bn"
              >বাংলাদেশ সরকারের ডেটা সুরক্ষা মানদণ্ড পূরণের জন্য নির্মিত</span
            >
            <span class="lang-en"
              >Built to meet Bangladesh government data security standards</span
            >
          </p>
        </div>
        <div class="row g-4">
          <div class="col-md-3 col-6">
            <div class="security-card">
              <div
                style="font-size: 2.2rem; color: #6ee7b7; margin-bottom: 12px"
              >
                <i class="bi bi-shield-lock-fill"></i>
              </div>
              <h6 class="text-white fw-bold">256-bit SSL</h6>
              <p
                style="
                  color: rgba(255, 255, 255, 0.6);
                  font-size: 0.85rem;
                  margin: 0;
                "
              >
                <span class="lang-bn"
                  >সমস্ত ডেটা স্থানান্তরের জন্য এন্ড-টু-এন্ড এনক্রিপশন</span
                >
                <span class="lang-en"
                  >End-to-end encryption for all data transfers</span
                >
              </p>
            </div>
          </div>
          <div class="col-md-3 col-6">
            <div class="security-card">
              <div
                style="font-size: 2.2rem; color: #6ee7b7; margin-bottom: 12px"
              >
                <i class="bi bi-cloud-check-fill"></i>
              </div>
              <h6 class="text-white fw-bold">ISO 27001</h6>
              <p
                style="
                  color: rgba(255, 255, 255, 0.6);
                  font-size: 0.85rem;
                  margin: 0;
                "
              >
                <span class="lang-bn"
                  >তথ্য সুরক্ষা ব্যবস্থাপনায় আন্তর্জাতিক মানদণ্ড</span
                >
                <span class="lang-en"
                  >International standard for information security
                  management</span
                >
              </p>
            </div>
          </div>
          <div class="col-md-3 col-6">
            <div class="security-card">
              <div
                style="font-size: 2.2rem; color: #6ee7b7; margin-bottom: 12px"
              >
                <i class="bi bi-database-lock"></i>
              </div>
              <h6 class="text-white fw-bold">
                <span class="lang-bn">ডেটা ব্যাকআপ</span
                ><span class="lang-en">Daily Backups</span>
              </h6>
              <p
                style="
                  color: rgba(255, 255, 255, 0.6);
                  font-size: 0.85rem;
                  margin: 0;
                "
              >
                <span class="lang-bn"
                  >স্বয়ংক্রিয় দৈনিক ব্যাকআপ, ৩০ দিনের ইতিহাস</span
                >
                <span class="lang-en"
                  >Automated daily backups with 30-day retention</span
                >
              </p>
            </div>
          </div>
          <div class="col-md-3 col-6">
            <div class="security-card">
              <div
                style="font-size: 2.2rem; color: #6ee7b7; margin-bottom: 12px"
              >
                <i class="bi bi-person-lock"></i>
              </div>
              <h6 class="text-white fw-bold">
                <span class="lang-bn">ভূমিকা-ভিত্তিক অ্যাক্সেস</span
                ><span class="lang-en">Role-Based Access</span>
              </h6>
              <p
                style="
                  color: rgba(255, 255, 255, 0.6);
                  font-size: 0.85rem;
                  margin: 0;
                "
              >
                <span class="lang-bn"
                  >প্রতিটি ব্যবহারকারীর ভূমিকার জন্য সূক্ষ্ম অনুমতি</span
                >
                <span class="lang-en"
                  >Fine-grained permissions for every user role</span
                >
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== AWARDS ===== -->
    <section class="py-5 awards-section" id="awards-section">
      <div class="container">
        <div class="text-center mb-5">
          <span class="sec-badge">
            <span class="lang-bn">স্বীকৃতি ও পুরস্কার</span
            ><span class="lang-en">Recognition &amp; Awards</span>
          </span>
          <h2 class="fw-bold">
            <span class="lang-bn">জাতীয় ও আন্তর্জাতিক স্বীকৃতি</span>
            <span class="lang-en"
              >Nationally &amp; Internationally Recognised</span
            >
          </h2>
        </div>
        <div class="row g-4">
          <div class="col-md-3 col-6">
            <div class="award-card">
              <div class="award-icon"><i class="bi bi-trophy-fill"></i></div>
              <h6>
                <span class="lang-bn">জাতীয় ই-গভ পুরস্কার</span
                ><span class="lang-en">National e-Gov Award</span>
              </h6>
              <p>
                <span class="lang-bn">সেরা সরকারি ডিজিটাল সেবা — ২০২৪</span
                ><span class="lang-en"
                  >Best Government Digital Service — 2024</span
                >
              </p>
            </div>
          </div>
          <div class="col-md-3 col-6">
            <div class="award-card">
              <div class="award-icon"><i class="bi bi-award-fill"></i></div>
              <h6>UNESCO ICT in Education</h6>
              <p>
                <span class="lang-bn">শ্রেষ্ঠ উদ্ভাবন ২০২৩</span
                ><span class="lang-en">Best Innovation 2023</span>
              </p>
            </div>
          </div>
          <div class="col-md-3 col-6">
            <div class="award-card">
              <div class="award-icon">
                <i class="bi bi-patch-check-fill"></i>
              </div>
              <h6>a2i Digital Champion</h6>
              <p>
                <span class="lang-bn">ডিজিটাল বাংলাদেশ উদ্যোগ স্বীকৃতি</span
                ><span class="lang-en"
                  >Digital Bangladesh Initiative Recognition</span
                >
              </p>
            </div>
          </div>
          <div class="col-md-3 col-6">
            <div class="award-card">
              <div class="award-icon"><i class="bi bi-star-fill"></i></div>
              <h6>
                <span class="lang-bn">শ্রেষ্ঠ এডটেক প্ল্যাটফর্ম</span
                ><span class="lang-en">Best EdTech Platform</span>
              </h6>
              <p>
                <span class="lang-bn">বাংলাদেশ টেক অ্যাওয়ার্ড ২০২৩</span
                ><span class="lang-en">Bangladesh Tech Awards 2023</span>
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== TESTIMONIALS ===== -->
    <section class="py-5 testimonial-section" id="testimonials-section">
      <div class="container">
        <div class="text-center mb-5">
          <span class="sec-badge sec-badge-light">
            <span class="lang-bn">অভিজ্ঞতার কথা</span
            ><span class="lang-en">Testimonials</span>
          </span>
          <h2 class="fw-bold text-white">
            <span class="lang-bn">সারাদেশের শিক্ষাবিদদের বিশ্বাস</span>
            <span class="lang-en">Trusted by Educators Nationwide</span>
          </h2>
          <p style="color: rgba(255, 255, 255, 0.6)">
            <span class="lang-bn"
              >স্কুলের অধ্যক্ষ ও কর্মকর্তারা EMS সম্পর্কে যা বলেন</span
            >
            <span class="lang-en"
              >What school principals and officials say about EMS</span
            >
          </p>
        </div>
        <div class="row g-4">
          <div class="col-md-4">
            <div class="testimonial-card">
              <div class="stars mb-3">★★★★★</div>
              <p
                style="
                  color: rgba(255, 255, 255, 0.85);
                  font-size: 0.95rem;
                  line-height: 1.7;
                "
              >
                <span class="lang-bn"
                  >"EMS আমাদের উপস্থিতি ও ফলাফল ব্যবস্থাপনায় সম্পূর্ণ
                  পরিবর্তন এনেছে। যা আগে সপ্তাহ লাগতো, এখন ঘণ্টায় হয়।"</span
                >
                <span class="lang-en"
                  >"EMS has completely transformed how we manage attendance
                  and results. What used to take weeks now takes hours."</span
                >
              </p>
              <div class="d-flex align-items-center mt-3 gap-3">
                <img
                  src="https://randomuser.me/api/portraits/men/32.jpg"
                  alt="Principal"
                  class="testimonial-avatar"
                />
                <div>
                  <div class="text-white fw-bold" style="font-size: 0.9rem">
                    Md. Karimul Islam
                  </div>
                  <div
                    style="color: rgba(255, 255, 255, 0.5); font-size: 0.8rem"
                  >
                    <span class="lang-bn"
                      >অধ্যক্ষ, রাজশাহী সরকারি উচ্চ বিদ্যালয়</span
                    ><span class="lang-en"
                      >Principal, Rajshahi Govt. High School</span
                    >
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="testimonial-card">
              <div class="stars mb-3">★★★★★</div>
              <p
                style="
                  color: rgba(255, 255, 255, 0.85);
                  font-size: 0.95rem;
                  line-height: 1.7;
                "
              >
                <span class="lang-bn"
                  >"অভিভাবক পোর্টাল আমাদের কর্মীদের ও পরিবারের মধ্যে যোগাযোগ
                  ব্যাপকভাবে উন্নত করেছে। অভিভাবকরা রিয়েল-টাইম আপডেট পেতে
                  ভালোবাসেন।"</span
                >
                <span class="lang-en"
                  >"The parent portal has improved communication between our
                  staff and families enormously. Parents love receiving
                  real-time updates."</span
                >
              </p>
              <div class="d-flex align-items-center mt-3 gap-3">
                <img
                  src="https://randomuser.me/api/portraits/women/44.jpg"
                  alt="Teacher"
                  class="testimonial-avatar"
                />
                <div>
                  <div class="text-white fw-bold" style="font-size: 0.9rem">
                    Fatema Begum
                  </div>
                  <div
                    style="color: rgba(255, 255, 255, 0.5); font-size: 0.8rem"
                  >
                    <span class="lang-bn"
                      >প্রধান শিক্ষিকা, চট্টগ্রাম মডেল কলেজ</span
                    ><span class="lang-en"
                      >Head Teacher, Chittagong Model College</span
                    >
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="testimonial-card">
              <div class="stars mb-3">★★★★★</div>
              <p
                style="
                  color: rgba(255, 255, 255, 0.85);
                  font-size: 0.95rem;
                  line-height: 1.7;
                "
              >
                <span class="lang-bn"
                  >"একজন জেলা শিক্ষা কর্মকর্তা হিসেবে, বিশ্লেষণ ড্যাশবোর্ড আমাকে
                  এক নজরে প্রতিটি বিদ্যালয়ের কার্যক্ষমতা দেখার সুযোগ
                  দেয়।"</span
                >
                <span class="lang-en"
                  >"As a district education officer, the analytics dashboard
                  gives me visibility into every school's performance at a
                  glance."</span
                >
              </p>
              <div class="d-flex align-items-center mt-3 gap-3">
                <img
                  src="https://randomuser.me/api/portraits/men/67.jpg"
                  alt="Officer"
                  class="testimonial-avatar"
                />
                <div>
                  <div class="text-white fw-bold" style="font-size: 0.9rem">
                    Abdul Mannan
                  </div>
                  <div
                    style="color: rgba(255, 255, 255, 0.5); font-size: 0.8rem"
                  >
                    <span class="lang-bn">জেলা শিক্ষা কর্মকর্তা, সিলেট</span
                    ><span class="lang-en">DEO, Sylhet District</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== MOBILE APP ===== -->
    <section class="py-5 mobile-section">
      <div class="container">
        <div class="row align-items-center g-5">
          <div class="col-lg-6 order-lg-2">
            <span class="sec-badge"
              >📱 <span class="lang-bn">মোবাইল অ্যাপ</span
              ><span class="lang-en">Mobile App</span></span
            >
            <h2 class="fw-bold">
              <span class="lang-bn">আপনার ফোন থেকে সবকিছু পরিচালনা করুন</span>
              <span class="lang-en">Manage Everything From Your Phone</span>
            </h2>
            <p class="text-muted">
              <span class="lang-bn"
                >আমাদের নেটিভ Android ও iOS অ্যাপ শিক্ষক, অভিভাবক এবং প্রশাসকদের
                যেকোনো জায়গায় সংযুক্ত রাখে — এমনকি কম-ব্যান্ডউইথ
                এলাকায়ও।</span
              >
              <span class="lang-en"
                >Our native Android and iOS apps keep teachers, parents, and
                admins connected anywhere, anytime — even in low-bandwidth
                areas.</span
              >
            </p>
            <div class="row g-3 mt-2">
              <div class="col-6">
                <div class="glass-card p-3">
                  <i class="bi bi-phone text-primary fs-4"></i>
                  <div class="fw-semibold mt-2">
                    <span class="lang-bn">অফলাইনে কাজ করে</span
                    ><span class="lang-en">Works Offline</span>
                  </div>
                  <div class="small text-muted">
                    <span class="lang-bn">সংযুক্ত হলে সিঙ্ক</span
                    ><span class="lang-en">Sync when connected</span>
                  </div>
                </div>
              </div>
              <div class="col-6">
                <div class="glass-card p-3">
                  <i class="bi bi-translate text-primary fs-4"></i>
                  <div class="fw-semibold mt-2">
                    <span class="lang-bn">বাংলা সাপোর্ট</span
                    ><span class="lang-en">Bangla Support</span>
                  </div>
                  <div class="small text-muted">
                    <span class="lang-bn">পূর্ণ ইউনিকোড বাংলা UI</span
                    ><span class="lang-en">Full Unicode Bangla UI</span>
                  </div>
                </div>
              </div>
              <div class="col-6">
                <div class="glass-card p-3">
                  <i class="bi bi-bell-fill text-primary fs-4"></i>
                  <div class="fw-semibold mt-2">
                    <span class="lang-bn">পুশ সতর্কতা</span
                    ><span class="lang-en">Push Alerts</span>
                  </div>
                  <div class="small text-muted">
                    <span class="lang-bn">তাৎক্ষণিক নোটিফিকেশন</span
                    ><span class="lang-en">Instant notifications</span>
                  </div>
                </div>
              </div>
              <div class="col-6">
                <div class="glass-card p-3">
                  <i class="bi bi-qr-code text-primary fs-4"></i>
                  <div class="fw-semibold mt-2">
                    <span class="lang-bn">QR উপস্থিতি</span
                    ><span class="lang-en">QR Attendance</span>
                  </div>
                  <div class="small text-muted">
                    <span class="lang-bn">কোনো হার্ডওয়্যার দরকার নেই</span
                    ><span class="lang-en">No hardware needed</span>
                  </div>
                </div>
              </div>
            </div>
            <div class="d-flex gap-3 mt-4">
              <a href="#" class="btn btn-dark rounded-pill px-4"
                ><i class="bi bi-google-play me-1"></i> Google Play</a
              >
              <a href="#" class="btn btn-dark rounded-pill px-4"
                ><i class="bi bi-apple me-1"></i> App Store</a
              >
            </div>
          </div>
          <div class="col-lg-6 order-lg-1 text-center">
            <img
              src="https://images.unsplash.com/photo-1551650975-87deedd944c3?w=540&auto=format&fit=crop&q=80"
              alt="Mobile App"
              class="img-fluid"
              style="
                border-radius: 24px;
                box-shadow: var(--shadow-lg);
                max-height: 480px;
                object-fit: cover;
              "
            />
          </div>
        </div>
      </div>
    </section>

    <!-- ===== ROADMAP ===== -->
    <section class="py-5 roadmap-section" id="roadmap-section">
      <div class="container">
        <div class="text-center mb-5">
          <span class="sec-badge">
            <span class="lang-bn">রোডম্যাপ</span
            ><span class="lang-en">Roadmap</span>
          </span>
          <h2 class="fw-bold">
            <span class="lang-bn">আসছে কি কি নতুন ফিচার</span>
            <span class="lang-en">What's Coming Next</span>
          </h2>
          <p class="text-muted">
            <span class="lang-bn">আমাদের পণ্য উন্নয়নের পরিকল্পনা দেখুন</span>
            <span class="lang-en">Our product development roadmap</span>
          </p>
        </div>
        <div class="row justify-content-center">
          <div class="col-lg-8">
            <div class="roadmap-item">
              <div class="roadmap-dot done"></div>
              <div>
                <h6>
                  <span class="lang-bn">✅ EMIS ২.০ ইন্টিগ্রেশন</span
                  ><span class="lang-en">✅ EMIS 2.0 Integration</span>
                </h6>
                <p>
                  <span class="lang-bn"
                    >শিক্ষা মন্ত্রণালয়ের নতুন EMIS সিস্টেমের সাথে সম্পূর্ণ API
                    সংযোগ — সম্পন্ন</span
                  ><span class="lang-en"
                    >Full API connection with Ministry of Education's new EMIS
                    system — Completed</span
                  >
                </p>
              </div>
              <span
                class="badge"
                style="
                  background: rgba(32, 201, 151, 0.15);
                  color: #20c997;
                  white-space: nowrap;
                  margin-left: auto;
                "
                ><span class="lang-bn">সম্পন্ন</span
                ><span class="lang-en">Done</span></span
              >
            </div>
            <div class="roadmap-item">
              <div class="roadmap-dot done"></div>
              <div>
                <h6>
                  <span class="lang-bn">✅ AI-ভিত্তিক উপস্থিতি বিশ্লেষণ</span
                  ><span class="lang-en"
                    >✅ AI-Powered Attendance Analytics</span
                  >
                </h6>
                <p>
                  <span class="lang-bn"
                    >মেশিন লার্নিং দিয়ে ঝুঁকিপূর্ণ শিক্ষার্থী শনাক্তকরণ —
                    সম্পন্ন</span
                  ><span class="lang-en"
                    >Machine learning to identify at-risk students —
                    Completed</span
                  >
                </p>
              </div>
              <span
                class="badge"
                style="
                  background: rgba(32, 201, 151, 0.15);
                  color: #20c997;
                  white-space: nowrap;
                  margin-left: auto;
                "
                ><span class="lang-bn">সম্পন্ন</span
                ><span class="lang-en">Done</span></span
              >
            </div>
            <div class="roadmap-item">
              <div class="roadmap-dot" style="background: var(--primary)"></div>
              <div>
                <h6>
                  <span class="lang-bn">🚀 ভয়েস-সক্ষম বাংলা চ্যাটবট</span
                  ><span class="lang-en">🚀 Voice-enabled Bangla Chatbot</span>
                </h6>
                <p>
                  <span class="lang-bn"
                    >অভিভাবক ও শিক্ষকদের জন্য বাংলা ভয়েস ইন্টারফেস —
                    চলমান</span
                  ><span class="lang-en"
                    >Bangla voice interface for parents and teachers — In
                    Progress</span
                  >
                </p>
              </div>
              <span
                class="badge"
                style="
                  background: rgba(25, 135, 84, 0.15);
                  color: var(--primary);
                  white-space: nowrap;
                  margin-left: auto;
                "
                ><span class="lang-bn">চলমান</span
                ><span class="lang-en">In Progress</span></span
              >
            </div>
            <div class="roadmap-item">
              <div class="roadmap-dot upcoming"></div>
              <div>
                <h6>
                  <span class="lang-bn">🔮 ডিজিটাল মার্কশিট (JSC/SSC/HSC)</span
                  ><span class="lang-en"
                    >🔮 Digital Marksheets (JSC/SSC/HSC)</span
                  >
                </h6>
                <p>
                  <span class="lang-bn"
                    >শিক্ষা বোর্ডের সাথে সরাসরি ফলাফল প্রকাশ ও ডিজিটাল সনদ —
                    আসছে</span
                  ><span class="lang-en"
                    >Direct result publication &amp; digital certificate with
                    Education Board — Coming Soon</span
                  >
                </p>
              </div>
              <span
                class="badge"
                style="
                  background: rgba(255, 193, 7, 0.15);
                  color: #ffc107;
                  white-space: nowrap;
                  margin-left: auto;
                "
                ><span class="lang-bn">আসছে</span
                ><span class="lang-en">Coming Soon</span></span
              >
            </div>
            <div class="roadmap-item">
              <div class="roadmap-dot upcoming"></div>
              <div>
                <h6>
                  <span class="lang-bn">🔮 অফলাইন ডেস্কটপ অ্যাপ</span
                  ><span class="lang-en">🔮 Offline Desktop App</span>
                </h6>
                <p>
                  <span class="lang-bn"
                    >ইন্টারনেট ছাড়াও পূর্ণ কার্যকারিতা সহ ডেস্কটপ
                    অ্যাপ্লিকেশন</span
                  ><span class="lang-en"
                    >Full-featured desktop application that works without
                    internet</span
                  >
                </p>
              </div>
              <span
                class="badge"
                style="
                  background: rgba(255, 193, 7, 0.15);
                  color: #ffc107;
                  white-space: nowrap;
                  margin-left: auto;
                "
                ><span class="lang-bn">পরিকল্পিত</span
                ><span class="lang-en">Planned</span></span
              >
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== FAQ ===== -->
    <section class="py-5 faq-section" id="faq-section">
      <div class="container">
        <div class="text-center mb-5">
          <span class="sec-badge">FAQ</span>
          <h2 class="fw-bold">
            <span class="lang-bn">প্রায়শই জিজ্ঞাসিত প্রশ্ন</span>
            <span class="lang-en">Frequently Asked Questions</span>
          </h2>
          <p class="text-muted">
            <span class="lang-bn"
              >বিদ্যালয় প্রশাসক ও কর্মকর্তাদের জন্য দ্রুত উত্তর</span
            >
            <span class="lang-en"
              >Quick answers for school administrators and officials</span
            >
          </p>
        </div>
        <div class="row justify-content-center">
          <div class="col-lg-8">
            <div class="accordion" id="faqAccordion">
              <div class="accordion-item">
                <h2 class="accordion-header">
                  <button
                    class="accordion-button"
                    data-bs-toggle="collapse"
                    data-bs-target="#faq1"
                  >
                    <span class="lang-bn"
                      >EMS কি শিক্ষা মন্ত্রণালয় কর্তৃক অনুমোদিত?</span
                    >
                    <span class="lang-en"
                      >Is EMS approved by the Ministry of Education?</span
                    >
                  </button>
                </h2>
                <div
                  id="faq1"
                  class="accordion-collapse collapse show"
                  data-bs-parent="#faqAccordion"
                >
                  <div class="accordion-body">
                    <span class="lang-bn"
                      >হ্যাঁ, EMS SaaS শিক্ষা মন্ত্রণালয়ের জাতীয় ডিজিটাল
                      শিক্ষা আর্কিটেকচার (NDEA) উদ্যোগের অধীনে একটি
                      আনুষ্ঠানিকভাবে নিবন্ধিত প্ল্যাটফর্ম।</span
                    >
                    <span class="lang-en"
                      >Yes, EMS SaaS is an officially registered platform
                      under the Ministry of Education's National Digital
                      Education Architecture (NDEA) initiative.</span
                    >
                  </div>
                </div>
              </div>
              <div class="accordion-item">
                <h2 class="accordion-header">
                  <button
                    class="accordion-button collapsed"
                    data-bs-toggle="collapse"
                    data-bs-target="#faq2"
                  >
                    <span class="lang-bn"
                      >EMIS কোড কী এবং আমি কীভাবে আমারটি খুঁজে পাব?</span
                    >
                    <span class="lang-en"
                      >What is the EMIS code and how do I find mine?</span
                    >
                  </button>
                </h2>
                <div
                  id="faq2"
                  class="accordion-collapse collapse"
                  data-bs-parent="#faqAccordion"
                >
                  <div class="accordion-body">
                    <span class="lang-bn"
                      >আপনার EMIS (শিক্ষা ব্যবস্থাপনা তথ্য সিস্টেম) কোড সরকার
                      কর্তৃক আপনার প্রতিষ্ঠানকে বরাদ্দ করা একটি অনন্য
                      পরিচয়কারী। আপনি এটি আপনার প্রতিষ্ঠানের অফিসিয়াল নিবন্ধন
                      সনদে পাবেন বা আপনার জেলা শিক্ষা কর্মকর্তার সাথে যোগাযোগ
                      করতে পারেন।</span
                    >
                    <span class="lang-en"
                      >Your EMIS code is a unique identifier assigned to your
                      institution by the government. You can find it on your
                      institution's official registration certificate or contact
                      your District Education Officer.</span
                    >
                  </div>
                </div>
              </div>
              <div class="accordion-item">
                <h2 class="accordion-header">
                  <button
                    class="accordion-button collapsed"
                    data-bs-toggle="collapse"
                    data-bs-target="#faq3"
                  >
                    <span class="lang-bn"
                      >সিস্টেমটি কি বাংলা ভাষা সমর্থন করে?</span
                    >
                    <span class="lang-en"
                      >Does the system support Bangla language?</span
                    >
                  </button>
                </h2>
                <div
                  id="faq3"
                  class="accordion-collapse collapse"
                  data-bs-parent="#faqAccordion"
                >
                  <div class="accordion-body">
                    <span class="lang-bn"
                      >অবশ্যই। EMS রিপোর্ট, রেজাল্ট শিট, SMS নোটিফিকেশন এবং
                      মোবাইল অ্যাপ UI সহ সমস্ত মডিউলে পূর্ণ বাংলা (ইউনিকোড)
                      সমর্থন করে।</span
                    >
                    <span class="lang-en"
                      >Absolutely. EMS supports full Bangla (Unicode) across
                      all modules including reports, result sheets, SMS
                      notifications, and the mobile app UI.</span
                    >
                  </div>
                </div>
              </div>
              <div class="accordion-item">
                <h2 class="accordion-header">
                  <button
                    class="accordion-button collapsed"
                    data-bs-toggle="collapse"
                    data-bs-target="#faq4"
                  >
                    <span class="lang-bn"
                      >একটি অ্যাকাউন্ট থেকে কি একাধিক শাখা পরিচালনা করা
                      যায়?</span
                    >
                    <span class="lang-en"
                      >Can multiple branches be managed from one account?</span
                    >
                  </button>
                </h2>
                <div
                  id="faq4"
                  class="accordion-collapse collapse"
                  data-bs-parent="#faqAccordion"
                >
                  <div class="accordion-body">
                    <span class="lang-bn"
                      >হ্যাঁ, আমাদের মাল্টি-ব্রাঞ্চ ম্যানেজমেন্ট জেলা ও বিভাগীয়
                      শিক্ষা অফিসগুলোকে শ্রেণিবদ্ধ অনুমতি সহ একটি একক ড্যাশবোর্ড
                      থেকে একাধিক প্রতিষ্ঠান পরিচালনা করতে দেয়।</span
                    >
                    <span class="lang-en"
                      >Yes, our multi-branch management allows district and
                      divisional education offices to oversee multiple
                      institutions from a single dashboard with hierarchical
                      permissions.</span
                    >
                  </div>
                </div>
              </div>
              <div class="accordion-item">
                <h2 class="accordion-header">
                  <button
                    class="accordion-button collapsed"
                    data-bs-toggle="collapse"
                    data-bs-target="#faq5"
                  >
                    <span class="lang-bn"
                      >শিক্ষক ও প্রশাসনিক কর্মচারীদের জন্য কি প্রশিক্ষণ দেওয়া
                      হয়?</span
                    >
                    <span class="lang-en"
                      >Is training provided for teachers and admin staff?</span
                    >
                  </button>
                </h2>
                <div
                  id="faq5"
                  class="accordion-collapse collapse"
                  data-bs-parent="#faqAccordion"
                >
                  <div class="accordion-body">
                    <span class="lang-bn"
                      >আমরা অন-সাইট ট্রেনিং, বাংলায় ভিডিও টিউটোরিয়াল, একটি
                      ব্যাপক হেল্প সেন্টার এবং ২৪/৭ সাপোর্ট প্রদান করি। নতুন
                      প্রতিষ্ঠানগুলো প্রথম ৩০ দিনের জন্য ডেডিকেটেড অনবোর্ডিং
                      পায়।</span
                    >
                    <span class="lang-en"
                      >We provide on-site training, video tutorials in Bangla, a
                      comprehensive help center, and 24/7 support. New
                      institutions receive dedicated onboarding for the first 30
                      days.</span
                    >
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== CONTACT ===== -->
    <section class="py-5 contact-section" id="contact-section">
      <div class="container">
        <div class="text-center mb-5">
          <span class="sec-badge">
            <span class="lang-bn">যোগাযোগ করুন</span
            ><span class="lang-en">Get In Touch</span>
          </span>
          <h2 class="fw-bold">
            <span class="lang-bn">আমাদের সাথে কথা বলুন</span>
            <span class="lang-en">Talk to Our Team</span>
          </h2>
          <p class="text-muted">
            <span class="lang-bn"
              >ডেমো বুক করুন, প্রশ্ন করুন বা সাপোর্ট নিন</span
            >
            <span class="lang-en"
              >Book a demo, ask a question, or get support</span
            >
          </p>
        </div>
        <div class="row g-4">
          <div class="col-lg-5">
            <div class="contact-card h-100">
              <h5 class="fw-bold mb-4">
                <span class="lang-bn">যোগাযোগের তথ্য</span>
                <span class="lang-en">Contact Information</span>
              </h5>
              <div class="contact-info-item">
                <div class="contact-info-icon">
                  <i class="bi bi-geo-alt-fill"></i>
                </div>
                <div>
                  <h6>
                    <span class="lang-bn">ঠিকানা</span
                    ><span class="lang-en">Address</span>
                  </h6>
                  <p>
                    <span class="lang-bn"
                      >বাংলাদেশ সচিবালয়, শিক্ষা মন্ত্রণালয়, ঢাকা-১০০০</span
                    ><span class="lang-en"
                      >Bangladesh Secretariat, Ministry of Education,
                      Dhaka-1000</span
                    >
                  </p>
                </div>
              </div>
              <div class="contact-info-item">
                <div class="contact-info-icon">
                  <i class="bi bi-telephone-fill"></i>
                </div>
                <div>
                  <h6>
                    <span class="lang-bn">হেল্পলাইন</span
                    ><span class="lang-en">Helpline</span>
                  </h6>
                  <p>+880-2-55100737 | 16535</p>
                </div>
              </div>
              <div class="contact-info-item">
                <div class="contact-info-icon">
                  <i class="bi bi-envelope-fill"></i>
                </div>
                <div>
                  <h6>
                    <span class="lang-bn">ইমেইল</span
                    ><span class="lang-en">Email</span>
                  </h6>
                  <p>support@ems.gov.bd</p>
                </div>
              </div>
              <div class="contact-info-item">
                <div class="contact-info-icon">
                  <i class="bi bi-clock-fill"></i>
                </div>
                <div>
                  <h6>
                    <span class="lang-bn">সাপোর্ট সময়</span
                    ><span class="lang-en">Support Hours</span>
                  </h6>
                  <p>
                    24/7
                    <span class="lang-bn"
                      >অনলাইন | সরকারি ছুটি ব্যতীত ৯টা-৫টা অফিস</span
                    ><span class="lang-en"
                      >Online | 9AM–5PM Office (Except Govt. Holidays)</span
                    >
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-7">
            <div class="contact-card">
              <h5 class="fw-bold mb-4">
                <span class="lang-bn">ডেমো রিকোয়েস্ট করুন</span>
                <span class="lang-en">Request a Demo</span>
              </h5>
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label"
                    ><span class="lang-bn">পূর্ণ নাম</span
                    ><span class="lang-en">Full Name</span></label
                  >
                  <input
                    type="text"
                    class="form-control"
                    placeholder="Md. Abdul Karim"
                  />
                </div>
                <div class="col-md-6">
                  <label class="form-label"
                    ><span class="lang-bn">পদবি</span
                    ><span class="lang-en">Designation</span></label
                  >
                  <input
                    type="text"
                    class="form-control"
                    placeholder="Principal / DEO"
                  />
                </div>
                <div class="col-md-6">
                  <label class="form-label"
                    ><span class="lang-bn">প্রতিষ্ঠানের নাম</span
                    ><span class="lang-en">Institution Name</span></label
                  >
                  <input
                    type="text"
                    class="form-control"
                    placeholder="Dhaka Govt. High School"
                  />
                </div>
                <div class="col-md-6">
                  <label class="form-label">EMIS Code</label>
                  <input
                    type="text"
                    class="form-control"
                    placeholder="1234567"
                  />
                </div>
                <div class="col-md-6">
                  <label class="form-label"
                    ><span class="lang-bn">মোবাইল</span
                    ><span class="lang-en">Mobile</span></label
                  >
                  <input
                    type="tel"
                    class="form-control"
                    placeholder="+880 1X XX XX XXXX"
                  />
                </div>
                <div class="col-md-6">
                  <label class="form-label"
                    ><span class="lang-bn">জেলা</span
                    ><span class="lang-en">District</span></label
                  >
                  <select class="form-select">
                    <option>
                      <span class="lang-bn">জেলা বেছে নিন</span
                      ><span class="lang-en">Select District</span>
                    </option>
                    <option>Dhaka</option>
                    <option>Chittagong</option>
                    <option>Rajshahi</option>
                    <option>Sylhet</option>
                    <option>Khulna</option>
                    <option>Barisal</option>
                    <option>Rangpur</option>
                    <option>Mymensingh</option>
                  </select>
                </div>
                <div class="col-12">
                  <label class="form-label"
                    ><span class="lang-bn">বার্তা (ঐচ্ছিক)</span
                    ><span class="lang-en">Message (Optional)</span></label
                  >
                  <textarea
                    class="form-control"
                    rows="3"
                    placeholder="..."
                  ></textarea>
                </div>
                <div class="col-12">
                  <button class="btn btn-primary w-100 rounded-pill py-2">
                    <i class="bi bi-send-fill me-2"></i>
                    <span class="lang-bn">ডেমো রিকোয়েস্ট পাঠান</span>
                    <span class="lang-en">Send Demo Request</span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== CTA ===== -->
    <section class="py-5 cta-section text-white text-center">
      <div class="container position-relative">
        <span
          class="badge bg-light text-dark px-3 py-2 mb-3 rounded-pill d-inline-block"
          ><span class="lang-bn">আজই শুরু করুন</span
          ><span class="lang-en">Get Started Today</span>
        </span>
        <h2 class="fw-bold fs-1">
          <span class="lang-bn">আপনার প্রতিষ্ঠান ডিজিটাইজ করতে প্রস্তুত?</span>
          <span class="lang-en">Ready to Digitize Your Institution?</span>
        </h2>
        <p
          class="lead"
          style="
            color: rgba(255, 255, 255, 0.8);
            max-width: 550px;
            margin: 0 auto;
          "
        >
          <span class="lang-bn"
            >ইতিমধ্যে ২৩,০০০+ বিদ্যালয় জাতীয় প্ল্যাটফর্মে রয়েছে। সেটআপে ২৪
            ঘণ্টার কম সময় লাগে।</span
          >
          <span class="lang-en"
            >Join 23,000+ schools already on the national platform. Setup takes
            less than 24 hours.</span
          >
        </p>
        <div class="d-flex gap-3 justify-content-center mt-4 flex-wrap">
          <a
            href="#contact-section"
            class="btn btn-light btn-lg px-5 rounded-pill fw-semibold"
            style="color: var(--primary)"
          >
            <span class="lang-bn">ডেমো রিকোয়েস্ট করুন</span
            ><span class="lang-en">Request a Demo</span>
          </a>
          <a
            href="#contact-section"
            class="btn btn-outline-light btn-lg px-5 rounded-pill"
          >
            <span class="lang-bn">বিক্রয় দলের সাথে কথা বলুন</span
            ><span class="lang-en">Contact Sales</span>
          </a>
        </div>
      </div>
    </section>

    <!-- ===== FOOTER ===== -->
    <footer class="pt-5 pb-3">
      <div class="container">
        <div class="row">
          <div class="col-md-4 mb-4">
            <img
              src="https://placehold.co/140x36/198754/ffffff?text=EMS"
              alt="GovEdu Logo"
              class="mb-3 rounded"
              height="36"
            />
            <p class="small">
              <span class="lang-bn"
                >শিক্ষা মন্ত্রণালয় • জাতীয় ডিজিটাল আর্কিটেকচার<br />শিক্ষা
                ব্যবস্থাপনাকে সহজ, স্বচ্ছ ও কার্যকর করে তোলা।</span
              >
              <span class="lang-en"
                >Ministry of Education • National Digital Architecture<br />Making
                education management simple, transparent, and efficient.</span
              >
            </p>
            <div class="d-flex gap-3 fs-5">
              <a href="#"><i class="bi bi-facebook"></i></a>
              <a href="#"><i class="bi bi-twitter-x"></i></a>
              <a href="#"><i class="bi bi-linkedin"></i></a>
              <a href="#"><i class="bi bi-youtube"></i></a>
            </div>
          </div>
          <div class="col-md-2 mb-4">
            <h6 style="color: rgba(255, 255, 255, 0.9)">
              <span class="lang-bn">দ্রুত লিঙ্ক</span
              ><span class="lang-en">Quick Links</span>
            </h6>
            <ul class="list-unstyled small">
              <li class="mb-1">
                <a href="#"
                  ><span class="lang-bn">আমাদের সম্পর্কে</span
                  ><span class="lang-en">About Us</span></a
                >
              </li>
              <li class="mb-1">
                <a href="#"
                  ><span class="lang-bn">ব্লগ</span
                  ><span class="lang-en">Blog</span></a
                >
              </li>
              <li class="mb-1">
                <a href="#"
                  ><span class="lang-bn">ক্যারিয়ার</span
                  ><span class="lang-en">Careers</span></a
                >
              </li>
              <li class="mb-1">
                <a href="#"
                  ><span class="lang-bn">প্রেস</span
                  ><span class="lang-en">Press</span></a
                >
              </li>
            </ul>
          </div>
          <div class="col-md-3 mb-4">
            <h6 style="color: rgba(255, 255, 255, 0.9)">
              <span class="lang-bn">সাপোর্ট</span
              ><span class="lang-en">Support</span>
            </h6>
            <ul class="list-unstyled small">
              <li class="mb-1">
                <a href="#"
                  ><span class="lang-bn">হেল্প সেন্টার</span
                  ><span class="lang-en">Help Center</span></a
                >
              </li>
              <li class="mb-1">
                <a href="#"
                  ><span class="lang-bn">API ডকুমেন্টেশন</span
                  ><span class="lang-en">API Documentation</span></a
                >
              </li>
              <li class="mb-1">
                <a href="#"
                  ><span class="lang-bn">সিস্টেম স্ট্যাটাস</span
                  ><span class="lang-en">System Status</span></a
                >
              </li>
              <li class="mb-1">
                <a href="#"
                  ><span class="lang-bn">সাপোর্ট যোগাযোগ</span
                  ><span class="lang-en">Contact Support</span></a
                >
              </li>
            </ul>
          </div>
          <div class="col-md-3 mb-4">
            <h6 style="color: rgba(255, 255, 255, 0.9)">
              <span class="lang-bn">নিউজলেটার</span
              ><span class="lang-en">Newsletter</span>
            </h6>
            <p class="small">
              <span class="lang-bn"
                >নতুন ফিচার ও সরকারি পরিপত্র সম্পর্কে আপডেট পান।</span
              >
              <span class="lang-en"
                >Stay updated on new features and government circulars.</span
              >
            </p>
            <div class="input-group input-group-sm">
              <input
                type="email"
                class="form-control"
                placeholder="Email address"
              />
              <button class="btn btn-secondary" type="button">
                <span class="lang-bn">সদস্য হন</span
                ><span class="lang-en">Subscribe</span>
              </button>
            </div>
          </div>
        </div>
        <hr style="border-color: rgba(255, 255, 255, 0.1); margin-top: 8px" />
        <div class="text-center small" style="color: var(--footer-text)">
          &copy; 2026 EMS.
          <span class="lang-bn">সর্বস্বত্ব সংরক্ষিত।</span>
          <span class="lang-en">All rights reserved.</span>
          |
          <a href="#">
            <span class="lang-bn">গোপনীয়তা নীতি</span
            ><span class="lang-en">Privacy Policy</span>
          </a>
          |
          <a href="#">
            <span class="lang-bn">সেবার শর্তাবলী</span
            ><span class="lang-en">Terms of Service</span>
          </a>
        </div>
      </div>
    </footer>

    <!-- Scroll to Top -->
    <button class="scroll-top" id="scrollTop" aria-label="Scroll to top">
      <i class="bi bi-arrow-up"></i>
    </button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
      // ── LANGUAGE TOGGLE ──
      const html = document.documentElement;
      const langBtn = document.getElementById("langToggle");
      let currentLang = "bn";

      langBtn.addEventListener("click", () => {
        currentLang = currentLang === "bn" ? "en" : "bn";
        html.setAttribute("data-lang", currentLang);
        langBtn.textContent = currentLang === "bn" ? "EN" : "বাং";
        html.setAttribute("lang", currentLang === "bn" ? "bn" : "en");
      });

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