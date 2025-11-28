# Project Overview

This file provides a high-level overview and detailed specifics of the current project.

## Project Name: Commerinity

## Technology Stack:
- **Backend**: Laravel 12.x, PHP 8.2+, MySQL (located in `backend/`)
- **Frontend**: Nuxt 3, Vue 3, TypeScript, Tailwind CSS v4 (located in `frontend/`)
- **Admin Panel**: Filament 4.0 (intended, as per `backend/composer.json`)
- **Authentication**: Laravel Sanctum 4.2.0
- **Testing**: Pest 4.0.2, PHPUnit 12.3.5

## Installed Packages (Current `backend` project):
- filament/filament: 4.0
- filament/spatie-laravel-media-library-plugin: 4.0
- laravel/framework: ^12.0
- laravel/tinker: ^2.10.1
- fakerphp/faker: ^1.23
- laravel/boost: ^1.8
- laravel/pail: ^1.2.2
- laravel/pint: ^1.24
- laravel/sail: ^1.41
- mockery/mockery: ^1.6
- nunomaduro/collision: ^8.6
- pestphp/pest: ^4.1
- pestphp/pest-plugin-laravel: ^4.0

## Key Models:
- App\Models\User
- Spatie\MediaLibrary\MediaCollections\Models\Media (confirmed FQCN)

## Project Goals (from README.md):
Commerinity is a modular, scalable platform that combines Multi-Level Marketing (MLM), eCommerce, Marketing Automation, and Content Management into a unified system.

## Current Feature Goal:
Implement a "WordPress-like" media manager for `spatie/laravel-medialibrary` within the current `backend` project's Filament v4 admin panel, integrated with the `RichEditor` component.

## Key Folders for Deep Understanding (from gemini.md):
- root/*
- docs/*
- plans/*
- .gemini/*
- backend/*
- backend/app/*
- backend/database/*
- client/* (or frontend/* if applicable)