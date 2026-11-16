@extends('layouts.site', ['bodyClass' => 'home inner-page contact-page', 'title' => 'تماس با ما', 'preload' => setting('contact_page.hero_image'), 'description' => setting('contact_page.hero_lead')])

@php
    $subjects = array_values(array_filter(array_map('trim', preg_split('/\r?\n/', (string) setting('contact_page.subjects')))));
@endphp

@section('content')
  @include('partials.page-hero', [
      'group' => 'contact_page',
      'crumbs' => [['صفحه اصلی', route('home')], ['تماس با ما', null]],
      'chips' => [['clock', setting('contact.response_time')], ['pin', setting('contact.address')]],
  ])

  <!-- ============ CONTACT CARDS ============ -->
  <section class="contact-cards" aria-label="راه‌های ارتباطی">
    <div class="container cc-grid stagger">
      <a class="cc-card" href="mailto:{{ setting('contact.email') }}"><span class="feature-ico"><svg aria-hidden="true"><use href="#i-mail"/></svg></span><small>ایمیل</small><b dir="ltr">{{ setting('contact.email') }}</b></a>
      <a class="cc-card" href="tel:{{ setting('contact.phone') }}"><span class="feature-ico"><svg aria-hidden="true"><use href="#i-phone"/></svg></span><small>تلفن</small><b dir="ltr">{{ setting('contact.phone_display') }}</b></a>
      @if (setting('contact.telegram_url'))
        <a class="cc-card" href="{{ setting('contact.telegram_url') }}" target="_blank" rel="noopener"><span class="feature-ico"><svg aria-hidden="true"><use href="#i-telegram"/></svg></span><small>تلگرام</small><b dir="ltr">{{ setting('contact.telegram_handle') }}</b></a>
      @endif
      @if (setting('contact.instagram_url'))
        <a class="cc-card" href="{{ setting('contact.instagram_url') }}" target="_blank" rel="noopener"><span class="feature-ico"><svg aria-hidden="true"><use href="#i-insta"/></svg></span><small>اینستاگرام</small><b dir="ltr">{{ setting('contact.instagram_handle') }}</b></a>
      @endif
    </div>
  </section>

  <!-- ============ FORM ============ -->
  <section class="section contact" id="contact" aria-labelledby="contact-title">
    <div class="container contact-grid">
      <div class="contact-copy">
        <p class="eyebrow reveal from-right" style="grid-column:1/-1"><svg aria-hidden="true"><use href="#i-gem"/></svg> فرم تماس</p>
        <h2 id="contact-title" class="h2 big" data-split>{{ heading(setting('home.contact_title')) }}</h2>
        <p class="lead reveal blur" style="--d:.35s">{{ setting('contact_page.form_lead') }}</p>
        <img class="contact-char" src="{{ asset('assets/img/characters/razino-companion.webp') }}" alt="" width="207" height="294" loading="lazy" data-x="140" data-speed="-0.25" data-depth="1.5">
        @if ($steps->isNotEmpty())
          <ul class="check-list contact-steps stagger">
            @foreach ($steps as $s)<li><svg aria-hidden="true"><use href="#i-check"/></svg><span><b>{{ $s->title }}</b>{{ $s->text }}</span></li>@endforeach
          </ul>
        @endif
      </div>
      @include('partials.contact-form', ['subjects' => $subjects])
    </div>
  </section>

  <!-- ============ PARENT SUPPORT ============ -->
  <section class="section night notch-both support" id="support" aria-labelledby="support-title">
    <img class="night-bg" src="{{ asset('assets/img/scenes/night-portal.webp') }}" alt="" width="1456" height="818" loading="lazy" data-parallax="0.08">
    <img class="night-char support-char" src="{{ asset('assets/img/characters/razino-wizard.webp') }}" alt="" width="221" height="395" loading="lazy" data-x="-160" data-speed="-0.25" data-depth="1.5">
    <div class="container">
      <div class="section-head">
        <div>
          <p class="eyebrow light reveal from-right"><svg aria-hidden="true"><use href="#i-gem"/></svg> {{ setting('contact_page.support_eyebrow') }}</p>
          <h2 id="support-title" class="h2 light" data-split>{{ heading(setting('contact_page.support_title')) }}</h2>
        </div>
        <p class="lead reveal blur" style="--d:.3s">{{ setting('contact_page.support_lead') }}</p>
      </div>
      <div class="feature-grid support-grid stagger">
        @foreach ($support as $b)
          <article class="feature-card"><span class="feature-ico"><svg aria-hidden="true"><use href="#i-{{ $b->icon ?: 'gem' }}"/></svg></span><h3>{{ $b->title }}</h3><p>{{ $b->text }}</p>@if ($b->link_url)<a class="more" href="{{ $b->link_url }}">{{ $b->link_label ?: 'بیشتر' }} <svg aria-hidden="true"><use href="#i-play"/></svg></a>@endif</article>
        @endforeach
      </div>
    </div>
  </section>

  @include('partials.faq', [
      'faqs' => $faqs, 'title' => setting('contact_page.faq_title'), 'lead' => setting('contact_page.faq_lead'),
      'character' => 'assets/img/characters/razino-merchant.webp',
  ])

  @include('partials.gallery')
@endsection
