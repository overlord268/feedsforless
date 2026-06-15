<template>
  <div class="funnel-page min-h-dvh flex flex-col bg-white">
    <main class="flex-grow flex flex-col lg:flex-row">
      <!-- Copy panel -->
      <section
        class="w-full lg:w-1/2 bg-[var(--funnel-inverse)] flex flex-col justify-center px-4 pt-10 pb-12 md:px-10 lg:px-16 xl:px-24 lg:min-h-[calc(100dvh-0px)] relative overflow-hidden order-1 lg:order-none"
      >
        <div class="absolute inset-0 funnel-hero-grid opacity-10 pointer-events-none" aria-hidden="true" />

        <!-- Mobile hero -->
        <div class="relative z-10 max-w-md mx-auto lg:max-w-xl w-full lg:hidden">
          <div class="inline-block px-2 py-1 mb-4 border border-[var(--funnel-primary)] text-[var(--funnel-primary)] funnel-overline text-xs font-bold uppercase tracking-widest">
            B2B Intelligence
          </div>
          <h1 class="font-headline text-2xl md:text-3xl font-extrabold text-white mb-4 leading-tight tracking-tight">
            STAY AHEAD THE FEED &amp; AGRICULTURAL MARKET
          </h1>
          <p class="text-base text-[var(--funnel-surface-variant)] mb-6 leading-relaxed font-normal">
            Subscribe to our newsletter and receive real-time price alerts, grain market trends, seeds, and fertilizers directly to your inbox.
          </p>
          <div class="w-full h-48 rounded overflow-hidden mb-2">
            <img
              :src="heroImageMobile"
              alt="Industrial grain elevator and storage facility"
              class="w-full h-full object-cover grayscale-[30%]"
              loading="lazy"
            />
          </div>
        </div>

        <!-- Desktop copy -->
        <div class="relative z-10 max-w-xl hidden lg:block">
          <div class="inline-flex items-center gap-3 mb-8">
            <span class="h-px w-10 bg-[var(--funnel-primary)] shrink-0" />
            <span class="funnel-overline text-[var(--funnel-primary-dim)] text-xs font-bold tracking-[0.2em] uppercase">
              — Elite Intelligence Network
            </span>
          </div>
          <h1 class="funnel-desktop-headline font-headline font-extrabold text-white uppercase mb-8">
            <span class="block">The Future of</span>
            <span class="block">Grain</span>
            <span class="block">Procurement</span>
            <span class="block">Is in Your</span>
            <span class="block">Inbox.</span>
          </h1>
          <p class="text-xl xl:text-2xl text-[var(--funnel-surface-variant)] mb-12 leading-relaxed font-normal">
            Subscribe to our newsletter and receive real-time price alerts, market trends for grains, seeds, and fertilizers directly to your inbox.
          </p>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
            <div class="space-y-3">
              <div class="funnel-feature-title text-[var(--funnel-primary-dim)] text-3xl font-extrabold tracking-tight">Weekly Reports</div>
              <p class="text-[var(--funnel-surface-variant)] text-sm leading-relaxed border-l border-[var(--funnel-primary)] pl-4">
                Supply and demand of commodities in your email
              </p>
            </div>
            <div class="space-y-3">
              <div class="funnel-feature-title text-[var(--funnel-primary-dim)] text-3xl font-extrabold tracking-tight">Exclusive Offers</div>
              <p class="text-[var(--funnel-surface-variant)] text-sm leading-relaxed border-l border-[var(--funnel-primary)] pl-4">
                Priority access to special inventories and wholesale discounts.
              </p>
            </div>
          </div>
        </div>
      </section>

      <!-- Form + visual -->
      <section
        class="w-full lg:w-1/2 funnel-industrial-bg flex items-center justify-center p-4 md:p-8 lg:p-16 relative order-2 lg:order-none min-h-[420px] lg:min-h-[calc(100dvh-0px)]"
      >
        <div class="absolute inset-0 bg-gradient-to-b from-[var(--funnel-inverse)]/50 to-transparent lg:hidden" aria-hidden="true" />

        <div class="lg:hidden w-full max-w-md mx-auto relative z-10 -mt-10">
          <div class="bg-white border border-[var(--funnel-outline-variant)] p-6 shadow-lg">
            <FunnelSubscribeForm variant="mobile" />
          </div>
        </div>

        <div class="hidden lg:block relative z-10 w-full max-w-md transform lg:-translate-x-[12%] xl:-translate-x-[20%]">
          <FunnelSubscribeForm variant="desktop" />
        </div>
      </section>
    </main>

    <!-- Mobile features -->
    <section class="lg:hidden py-12 px-4 bg-slate-100 order-3">
      <div class="max-w-md mx-auto grid gap-8">
        <article class="flex flex-col gap-4 p-4 border-l-2 border-[var(--funnel-primary)] bg-white">
          <div class="flex items-center gap-2 text-[var(--funnel-primary)]">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <h3 class="font-headline font-bold text-lg tracking-tight">Weekly Reports</h3>
          </div>
          <p class="text-sm text-slate-600 leading-relaxed">
            Comprehensive market analysis delivered every Monday morning.
          </p>
          <div class="h-32 rounded overflow-hidden">
            <img :src="featureImageReports" alt="Agricultural data charts" class="w-full h-full object-cover" loading="lazy" />
          </div>
        </article>

        <article class="flex flex-col gap-4 p-4 border-l-2 border-[#406182] bg-white">
          <div class="flex items-center gap-2 text-[#406182]">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 .953.386 1.414.914l4.586 4.586a2 2 0 010 2.828l-7.172 7.172a2 2 0 01-2.828 0l-4.586-4.586A2 2 0 013 12.07V7a4 4 0 014-4z" />
            </svg>
            <h3 class="font-headline font-bold text-lg tracking-tight">Exclusive Offers</h3>
          </div>
          <p class="text-sm text-slate-600 leading-relaxed">
            First-look access to bulk procurement deals on seeds and fertilizers.
          </p>
          <div class="h-32 rounded overflow-hidden">
            <img :src="featureImageOffers" alt="Industrial fertilizer" class="w-full h-full object-cover" loading="lazy" />
          </div>
        </article>
      </div>
    </section>

    <section class="lg:hidden py-8 bg-[var(--funnel-inverse)] text-[var(--funnel-primary-dim)] order-4">
      <div class="max-w-md mx-auto px-4 flex justify-around text-center">
        <div>
          <div class="font-headline funnel-stat-value text-2xl font-bold">15K+</div>
          <div class="text-xs font-bold uppercase opacity-70">Subscribers</div>
        </div>
        <div class="w-px h-12 bg-white/20" />
        <div>
          <div class="font-headline funnel-stat-value text-2xl font-bold">24/7</div>
          <div class="text-xs font-bold uppercase opacity-70">Price Monitoring</div>
        </div>
      </div>
    </section>

    <SiteFooter hide-company class="order-5" />

    <ChatWidget />
  </div>
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue';
import SiteFooter from '../../components/layout/SiteFooter.vue';
import ChatWidget from '../../components/chat/ChatWidget.vue';
import FunnelSubscribeForm from '../../components/funnel/FunnelSubscribeForm.vue';
import { installNotificationSoundUnlock } from '../../composables/useNotificationSound';
import '../../assets/styles/funnel.css';

const heroImageMobile =
  'https://lh3.googleusercontent.com/aida-public/AB6AXuCCxJmeH1rZBeQcw31ziiqrF8OmsXjvqhCVsJOvBb7I6O7SMqNA58p4vJC7zXImodOq1xLSYNDhRtGZ5LEjpmzBlXxOE5z-w6pJ3hvgv6StmId3HWvi5VWMo7jwyB2VGFJUc8cTpQgkQrXRtdQp6cWgsbLl2paltZkZj0b52gy_fC_LqiCPqanQGNl-UjUwZMIhG8a25gAkoopg_EHAjwtjkkBF6IKGFuBw6MrlqbOsXvGwPtmlG28dxbPMVI712yA902TjgLxLyTk';

const featureImageReports =
  'https://lh3.googleusercontent.com/aida-public/AB6AXuDypm0b6LtjJr2jTWynJ3_prQd9XogQI3Ezh0YEM_gq0CxVQxzSmGHXrzjj0kf1OdeDdtPryxpakMkzRO49tL2Y0hePFzfqq1KA175wlZIvoDgLTOZ4crB_O-EqgevflPXI1cIbxNJH9a-eH_wlCnOIDqG-_z1CXQih3zQX4YA0basQg1y-dDvTfGrLs9EfK5TA08uuL018coP-WcZ5Vm1rXttuU3IGBHJ0Gd2777TOisQ2fSWC_fGSp3D-_kVDoOQIv6ZHSEmCkuM';

const featureImageOffers =
  'https://lh3.googleusercontent.com/aida-public/AB6AXuCLrO4loTFxXTKaAzR5X0KcOPk_hdNPtWjD3m9o7inWcMcccj_yyFJ83SQXRTVfbwuzZdjKH1br2TFy2GvvpWLr1q_3CkekzPGkC_BA8tEOHW6NmSxPYG1JYEWajAMeq4MitRTNtBc6w6eM9nYNNsn9f8NJf1ENRlK51GI7Mi4d5bQLQ4p59doddksT4sMR3HOMSCMo3eZ1-Hs3BXPgQoRr8o-EwW0RfOzibV3MdaSMzAXQJBwJ_R4n9GP3o8ErQkza0FPnjCCZHEI';

const removeSoundUnlock = installNotificationSoundUnlock();

onMounted(() => {
  document.title = 'Market Insights | FeedsForLess';
});

onUnmounted(() => {
  removeSoundUnlock?.();
});
</script>
