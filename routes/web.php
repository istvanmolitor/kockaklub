<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Storefront\AccountController;
use App\Http\Controllers\Storefront\AccountPasswordController;
use App\Http\Controllers\Storefront\CartController;
use App\Http\Controllers\Storefront\CatalogController;
use App\Http\Controllers\Storefront\CheckoutController;
use App\Http\Controllers\Storefront\ContactController;
use App\Http\Controllers\Storefront\ContentController;
use App\Http\Controllers\Storefront\HomeController;
use App\Http\Controllers\Storefront\PostalCodeController;
use App\Http\Controllers\Storefront\SearchController;
use App\Http\Controllers\ArukeresoFeedController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/arukereso.xml', [ArukeresoFeedController::class, 'index'])->name('arukereso.feed');
Route::get('/llms.txt', fn () => response()->view('llms-txt')->header('Content-Type', 'text/plain'))->name('llms-txt');
Route::get('/robots.txt', fn () => response()->view('robots-txt')->header('Content-Type', 'text/plain'))->name('robots');

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/termekek', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/termekek/{product:slug}', [CatalogController::class, 'show'])->name('catalog.show');

Route::get('/kereses', [SearchController::class, 'index'])->name('search.index');

Route::get('/orszagok/{country}/iranyitoszamok', [PostalCodeController::class, 'index'])->name('postal-codes.index');

Route::get('/tartalom/{content:slug}', [ContentController::class, 'show'])->name('content.show');

Route::get('/kapcsolat', [ContactController::class, 'create'])->name('contact.create');
Route::post('/kapcsolat', [ContactController::class, 'store'])->name('contact.store');

Route::get('/kosar', [CartController::class, 'show'])->name('cart.show');
Route::post('/kosar/hozzaadas/{product}', [CartController::class, 'store'])->name('cart.store');
Route::patch('/kosar/{product}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/kosar/{product}', [CartController::class, 'destroy'])->name('cart.destroy');

Route::get('/penztar/kezdes', [CheckoutController::class, 'gate'])->name('checkout.gate');
Route::get('/penztar', [CheckoutController::class, 'create'])->name('checkout.create');
Route::post('/penztar', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/rendeles/{order:order_number}/visszaigazolas', [CheckoutController::class, 'confirmation'])->name('checkout.confirmation');

Route::middleware('guest')->group(function () {
    Route::get('/regisztracio', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/regisztracio', [RegisteredUserController::class, 'store']);

    Route::get('/bejelentkezes', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/bejelentkezes', [AuthenticatedSessionController::class, 'store']);

    Route::get('/elfelejtett-jelszo', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/elfelejtett-jelszo', [PasswordResetLinkController::class, 'store'])->name('password.email');

    Route::get('/jelszo-visszaallitas/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/jelszo-visszaallitas', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/kijelentkezes', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/email/verify', EmailVerificationPromptController::class)->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', VerifyEmailController::class)
        ->middleware('signed')
        ->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('/fiokom', [AccountController::class, 'show'])->middleware('verified')->name('account.show');
    Route::patch('/fiokom', [AccountController::class, 'update'])->middleware('verified')->name('account.update');
    Route::get('/fiokom/rendeleseim', [AccountController::class, 'orders'])->middleware('verified')->name('account.orders');

    Route::get('/fiokom/jelszo', [AccountPasswordController::class, 'edit'])->middleware('verified')->name('account.password.edit');
    Route::patch('/fiokom/jelszo', [AccountPasswordController::class, 'update'])->middleware('verified')->name('account.password.update');
});
