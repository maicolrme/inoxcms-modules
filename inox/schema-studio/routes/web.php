<?php

use Illuminate\Support\Facades\Route;
use Inox\SchemaStudio\Livewire\SchemaList;
use Inox\SchemaStudio\Livewire\SchemaDesigner;
use Inox\SchemaStudio\Livewire\GeneratedModelList;

Route::middleware(['web', 'auth'])->prefix('admin/schema-studio')->name('admin.schema-studio.')->group(function () {
    Route::get('/', SchemaList::class)->name('index');
    Route::get('/designer/{name}', SchemaDesigner::class)->name('designer');
    Route::get('/data/{model}', GeneratedModelList::class)->name('data');
});

Route::middleware(['web', 'auth'])->prefix('admin')->name('admin.schema-studio.')->group(function () {
    Route::get('/schema-models/{model}/create', GeneratedModelList::class)->name('create');
});
