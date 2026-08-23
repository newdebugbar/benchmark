<?php

it('supports the desktop review workflow', function (): void {
    visit('/trips/kyoto-autumn')
        ->on()->desktop()
        ->assertTitleContains('Kyoto in autumn')
        ->assertSee('Eight days, thoughtfully paced')
        ->assertCount('[data-testid="itinerary-day"]', 8)
        ->fill('Review note', 'Confirm the riverside lunch, then send the final journey to Elise.')
        ->press('Save note')
        ->assertSee('Saved')
        ->assertScript("document.querySelector('#newdebugbar') !== null")
        ->assertNoJavaScriptErrors()
        ->assertNoBrokenImages();
});

it('adapts the workspace to a phone', function (): void {
    visit('/trips/kyoto-autumn')
        ->on()->iPhone15Pro()
        ->assertSee('Kyoto in autumn')
        ->assertVisible('[aria-label="Open navigation"]')
        ->click('[aria-label="Open navigation"]')
        ->assertAttribute('[aria-label="Open navigation"]', 'aria-expanded', 'true')
        ->assertVisible('.primary-nav')
        ->assertSee('Overview')
        ->assertScript('window.innerWidth === 393')
        ->assertNoJavaScriptErrors()
        ->assertNoBrokenImages();
});

it('supports the interactive journey map', function (): void {
    visit('/trips/kyoto-autumn/map')
        ->on()->desktop()
        ->assertTitleContains('Kyoto in autumn map')
        ->assertSee('9 planned stops')
        ->assertCount('[data-testid="map-stop"]', 9)
        ->assertCount('.map-pin', 9)
        ->click('[data-testid="map-stop"]:nth-child(5)')
        ->assertSeeIn('.map-detail', 'Seasonal kaiseki dinner')
        ->click('Refresh stops')
        ->assertCount('[data-testid="map-stop"]', 9)
        ->assertNoJavaScriptErrors()
        ->assertNoBrokenImages();
});

it('shows pending communication lifecycle facts in adjacent inspector sections', function (): void {
    visit('/trips/kyoto-autumn/debug/communications/pending')
        ->on()->desktop()
        ->assertSee('Five real database jobs are waiting for a worker.')
        ->click('[data-ndb-window-controls="compact"] [data-ndb-window-action="expand"]')
        ->click('[data-ndb-section="queue"]')
        ->assertVisible('[data-ndb-section-panel="queue"]')
        ->assertSee('JourneyReviewReady')
        ->assertSee('JourneyReviewDeliveryProbe')
        ->assertSee('Waiting for worker')
        ->assertSee('Delayed')
        ->click('[data-ndb-section="notifications"]')
        ->assertVisible('[data-ndb-section-panel="notifications"]')
        ->assertSee('JourneyReviewReminder')
        ->assertSee('mail')
        ->click('[data-ndb-section="mail"]')
        ->assertVisible('[data-ndb-section-panel="mail"]')
        ->assertSee('JourneyReviewDeliveryProbe')
        ->assertNoJavaScriptErrors();
});

it('shows deferred work as after-response activity', function (): void {
    visit('/trips/kyoto-autumn/debug/communications/after-response')
        ->on()->desktop()
        ->assertSee('Deferred and dispatch-after-response mail will run after this page is ready.')
        ->click('[data-ndb-window-controls="compact"] [data-ndb-window-action="expand"]')
        ->click('[data-ndb-section="queue"]')
        ->assertVisible('[data-ndb-section-panel="queue"]')
        ->assertSee('SendJourneyReviewAfterResponse')
        ->assertSee('After response')
        ->click('[data-ndb-section="mail"]')
        ->assertVisible('[data-ndb-section-panel="mail"]')
        ->assertSee('After response')
        ->assertNoJavaScriptErrors();
});
