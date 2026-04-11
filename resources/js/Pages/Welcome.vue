<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});
</script>

<template>
    <Head title="Orienta+216 — Trouve ta voie" />

    <div class="page-wrapper">

        <!-- Floating 3D Education Elements -->
        <div class="floater floater-tl">🎓</div>
        <div class="floater floater-tr">📚</div>
        <div class="floater floater-bl">✏️</div>
        <div class="floater floater-br">🏛️</div>
        <div class="floater floater-mid-l">⭐</div>
        <div class="floater floater-mid-r">🔬</div>

        <!-- Main Card -->
        <div class="card">

            <!-- Top Logo Badge -->
            <div class="logo-badge">
                <span class="logo-dot"></span>
                <span class="logo-badge-text">Orienta+216</span>
            </div>

            <!-- Brand Name -->
            <div class="brand">
                <h1 class="brand-name">
                    <span class="brand-orienta">Orienta</span><span class="brand-plus">+216</span>
                </h1>
            </div>

            <!-- Headline -->
            <p class="eyebrow">DÉCOUVRE TA VOIE</p>
            <h2 class="headline">
                Trouve ton avenir<br />
                <span class="headline-accent">universitaire.</span>
            </h2>

            <p class="subtitle">
                Une orientation intelligente, personnalisée<br />
                pour chaque étudiant tunisien.
            </p>

            <!-- Single CTA Button -->
            <Link
                v-if="canLogin"
                :href="$page.props.auth.user ? route('etudiant.dashboard') : route('login')"
                class="cta-btn"
            >
                <span class="cta-icon"></span>
                <span>Explore Your Path</span>
                <span class="cta-arrow">›</span>
            </Link>

            <!-- Bottom Nav -->
            

        </div>

    </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500&display=swap');

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body { font-family: 'DM Sans', sans-serif; }

/* Page background */
.page-wrapper {
    min-height: 100vh;
    background: radial-gradient(ellipse at 20% 30%, #c8e6ff 0%, transparent 55%),
                radial-gradient(ellipse at 80% 70%, #d4e8ff 0%, transparent 55%),
                radial-gradient(ellipse at 50% 50%, #e8f4ff 0%, #daeeff 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
    padding: 2rem;
}

/* Floating elements */
.floater {
    position: absolute;
    font-size: 2.6rem;
    filter: drop-shadow(0 8px 20px rgba(30, 90, 200, 0.18));
    pointer-events: none;
    user-select: none;
}
.floater-tl  { top: 6%;  left: 5%;  animation: floatA 5s ease-in-out infinite; }
.floater-tr  { top: 5%;  right: 6%; animation: floatB 6s ease-in-out infinite; font-size: 3rem; }
.floater-bl  { bottom: 8%; left: 6%; animation: floatC 4.5s ease-in-out infinite; font-size: 2.2rem; }
.floater-br  { bottom: 6%; right: 5%; animation: floatA 7s ease-in-out infinite; font-size: 3.2rem; }
.floater-mid-l { top: 45%; left: 3%; animation: floatB 5.5s ease-in-out infinite; font-size: 1.8rem; }
.floater-mid-r { top: 40%; right: 3%; animation: floatC 6.5s ease-in-out infinite; font-size: 1.9rem; }

@keyframes floatA {
    0%, 100% { transform: translateY(0px) rotate(-5deg); }
    50%       { transform: translateY(-18px) rotate(5deg); }
}
@keyframes floatB {
    0%, 100% { transform: translateY(0px) rotate(8deg); }
    50%       { transform: translateY(-22px) rotate(-4deg); }
}
@keyframes floatC {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50%       { transform: translateY(-14px) rotate(10deg); }
}

/* Main card */
.card {
    background: rgba(255, 255, 255, 0.78);
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
    border: 1px solid rgba(255, 255, 255, 0.9);
    border-radius: 2rem;
    box-shadow:
        0 2px 4px rgba(30, 90, 200, 0.04),
        0 8px 32px rgba(30, 90, 200, 0.10),
        0 32px 80px rgba(30, 90, 200, 0.12);
    padding: 3.5rem 4rem;
    text-align: center;
    max-width: 560px;
    width: 100%;
    position: relative;
    z-index: 1;
    animation: cardIn 0.8s cubic-bezier(0.22, 1, 0.36, 1) both;
}

@keyframes cardIn {
    from { opacity: 0; transform: translateY(32px) scale(0.97); }
    to   { opacity: 1; transform: translateY(0)    scale(1);    }
}

/* Logo badge (top pill) */
.logo-badge {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: rgba(255,255,255,0.9);
    border: 1px solid rgba(30, 100, 200, 0.15);
    border-radius: 99px;
    padding: 6px 18px;
    margin-bottom: 2.2rem;
}
.logo-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    background: #1a6fea;
    display: inline-block;
}
.logo-badge-text {
    font-family: 'DM Sans', sans-serif;
    font-size: 13px;
    font-weight: 500;
    color: #1a6fea;
    letter-spacing: 0.02em;
}

/* Brand name */
.brand { margin-bottom: 0.5rem; }
.brand-name {
    font-family: 'Syne', sans-serif;
    font-size: 3.8rem;
    font-weight: 800;
    line-height: 1;
    letter-spacing: -0.03em;
}
.brand-orienta { color: #0f172a; }
.brand-plus {
    color: #1a6fea;
    font-size: 2.4rem;
    vertical-align: super;
    letter-spacing: -0.01em;
}

/* Eyebrow */
.eyebrow {
    font-size: 11px;
    font-weight: 500;
    letter-spacing: 0.14em;
    color: #94a3b8;
    margin-bottom: 0.6rem;
    margin-top: 1.6rem;
}

/* Headline */
.headline {
    font-family: 'Syne', sans-serif;
    font-size: 2.1rem;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.2;
    margin-bottom: 1rem;
}
.headline-accent { color: #1a6fea; }

/* Subtitle */
.subtitle {
    font-size: 15px;
    color: #64748b;
    line-height: 1.7;
    margin-bottom: 2.4rem;
    font-weight: 300;
}

/* CTA Button */
.cta-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: #0f172a;
    color: #fff;
    border-radius: 99px;
    padding: 14px 32px;
    font-family: 'DM Sans', sans-serif;
    font-size: 15px;
    font-weight: 500;
    letter-spacing: 0.01em;
    text-decoration: none;
    transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
    box-shadow: 0 4px 20px rgba(15, 23, 42, 0.25);
}
.cta-btn:hover {
    background: #1a6fea;
    transform: translateY(-2px);
    box-shadow: 0 8px 32px rgba(26, 111, 234, 0.38);
}
.cta-icon { font-size: 16px; }
.cta-arrow {
    font-size: 20px;
    font-weight: 300;
    line-height: 1;
    transition: transform 0.18s ease;
}
.cta-btn:hover .cta-arrow { transform: translateX(3px); }

/* Bottom nav */
.bottom-nav {
    margin-top: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    font-size: 13px;
}
.nav-link {
    color: #64748b;
    text-decoration: none;
    transition: color 0.15s;
}
.nav-link:hover { color: #1a6fea; }
.nav-sep { color: #cbd5e1; }

/* Responsive */
@media (max-width: 600px) {
    .card { padding: 2.5rem 2rem; }
    .brand-name { font-size: 2.8rem; }
    .headline { font-size: 1.6rem; }
    .floater { font-size: 1.8rem; }
    .floater-tr, .floater-br { font-size: 2rem; }
}
</style>