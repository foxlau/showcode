<?php

namespace Tests;

use Laravel\Dusk\Browser;
use Tests\Pages\App;
use Tests\Pages\Buy;

it('can load', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit(new App)
            ->assertSee('Untitled Project');
    });
});

it('can load buy page', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit(new Buy)
            ->assertSee('Design beautiful')
            ->assertSee('code screenshots');
    });
});

it('can toggle dark mode', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit(new App)
            ->assertVisible('.feather-sun')
            ->click('@button-toggle-dark')
            ->assertMissing('.feather-sun')
            ->assertVisible('.feather-moon')
            ->click('@button-toggle-dark')
            ->assertMissing('.feather-moon')
            ->assertVisible('.feather-sun');
    });
});

it('can add tabs', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit(new App)
            ->click('@button-add-tab')
            ->assertVisible('@tab-1')
            ->click('@button-add-tab')
            // Desktop only.
            ->assertMissing('@tab-2');
    });
});

it('can remove tabs', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit(new App)
            ->click('@button-add-tab')
            ->waitFor('@tab-1')
            ->within('@tab-1', function (Browser $tab) {
                $tab->click('@button-close-tab');
            })
            ->assertMissing('@tab-1')
            ->assertVisible('@tab-0');
    });
});

it('can rename tabs', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit(new App)
            ->mouseover('@tab-0')
            ->click('@button-edit-tab')
            ->type('@input-tab-name', 'Foo bar')
            ->click('@button-edit-tab')
            ->assertSeeIn('@tab-0', 'Foo bar');
    });
});

it('can resize editor pane', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit(new App);
        
        $browser->with('@page-0', function (Browser $browser) {
            $style = $browser->attribute('@editors', 'style');

            expect($style)->toEqual('width: calc(40% - 3px);');

            $browser->dragRight('.gutter-horizontal', 200);

            $style = $browser->attribute('@editors', 'style');

            expect($style)->toEqual('width: calc(50.4167% - 3px);');
        });
    });
});

it('can copy image', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit(new App);
        
        $browser->within('@page-0', function (Browser $browser) {
            $browser->click('@button-copy');
        });

        $browser->assertSee('Write permission denied.');
    });
});

it('can handle pages from previous version', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit(new App);

        $json = json_encode(
            json_decode(file_get_contents(__DIR__.'/fixtures/template.json'))
        );

        $browser->script(
            <<<JS
            window.localStorage.setItem('pages/fbd16ec6-75d3-40e1-b76a-de26a5906532', '$json');
            JS
        );

        $browser
            ->visit(new App)
            ->click('[data-tab-id="fbd16ec6-75d3-40e1-b76a-de26a5906532"]')
            ->within('[data-project-id="fbd16ec6-75d3-40e1-b76a-de26a5906532"]', function (Browser $browser) {
                $browser->assertSeeIn('@canvas', 'This is an example');
                $browser->assertVisible('@window-github-dark-dimmed');
            });
    });
});
