<?php

/*
|--------------------------------------------------------------------------
| Portfolio identity
|--------------------------------------------------------------------------
|
| Every value on the public portfolio that is not stored in the database lives
| here, so copy can be edited without touching Blade or PHP classes.
|
| Nothing in this file may claim a metric, a client, an employment record or a
| link that cannot be verified. Optional channels default to `enabled => false`
| and simply do not render.
|
*/

$whatsappNumber = '628815695295';
$whatsappMessage = rawurlencode('Hi Satria, I found your portfolio and would like to talk about a project.');

return [

    /*
    |--------------------------------------------------------------------------
    | Identity
    |--------------------------------------------------------------------------
    */

    'name' => 'Satria Rangga Jati',
    'short_name' => 'SRJ',
    'logo_mark' => 'SRJ',
    'role' => 'Full Stack Developer',
    'role_secondary' => 'AI-Powered Web Applications',
    'tagline' => 'I build production-ready web applications end to end — interface, backend, AI services and Linux deployment.',

    'description' => 'Full Stack Developer specialising in AI-powered web applications, built with Laravel, Vue and TypeScript, Python and FastAPI, deployed on Linux.',

    'location' => 'Indonesia',
    'availability' => 'Open to full-time roles and project work',

    /*
    |--------------------------------------------------------------------------
    | Self-registration
    |--------------------------------------------------------------------------
    |
    | Every authenticated account can reach /dashboard and therefore edit all
    | portfolio content, so public registration is closed by default. Existing
    | user accounts keep working; only the ability to create new ones is gated.
    |
    */

    'allow_registration' => env('ALLOW_REGISTRATION', false),

    /*
    |--------------------------------------------------------------------------
    | Primary calls to action
    |--------------------------------------------------------------------------
    */

    'resume_url' => env('PORTFOLIO_RESUME_URL'),

    /*
    |--------------------------------------------------------------------------
    | Contact channels
    |--------------------------------------------------------------------------
    |
    | WhatsApp, Instagram, GitHub and the website are the channels that were
    | already present in the previous version of this site, so they are enabled.
    | Facebook and LinkedIn are kept but disabled: their URLs exist somewhere in
    | the old source, but they were never verified, so nothing renders until the
    | owner explicitly turns them on. They are never guessed.
    |
    */

    'contact' => [

        'whatsapp' => [
            'enabled' => true,
            'label' => 'WhatsApp',
            'handle' => '+62 881-5695-295',
            'url' => 'https://wa.me/'.$whatsappNumber.'?text='.$whatsappMessage,
        ],

        'github' => [
            'enabled' => true,
            'label' => 'GitHub',
            'handle' => 'satriaranggaj',
            'url' => 'https://github.com/satriaranggaj',
        ],

        'instagram' => [
            'enabled' => true,
            'label' => 'Instagram',
            'handle' => '@satria_rangga_j',
            'url' => 'https://www.instagram.com/satria_rangga_j',
        ],

        'website' => [
            'enabled' => true,
            'label' => 'Website',
            'handle' => 'satriarangga.my.id',
            'url' => 'https://satriarangga.my.id',
        ],

        'email' => [
            'enabled' => false,
            'label' => 'Email',
            'handle' => env('PORTFOLIO_EMAIL'),
            'url' => env('PORTFOLIO_EMAIL') ? 'mailto:'.env('PORTFOLIO_EMAIL') : null,
        ],

        'linkedin' => [
            'enabled' => false,
            'label' => 'LinkedIn',
            'handle' => 'satriaranggamyid',
            'url' => 'https://www.linkedin.com/in/satriaranggamyid/',
        ],

        'facebook' => [
            'enabled' => false,
            'label' => 'Facebook',
            'handle' => 'satriaranggajati.jati',
            'url' => 'https://web.facebook.com/satriaranggajati.jati',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | What I build
    |--------------------------------------------------------------------------
    |
    | Descriptions describe the category of work, not individual projects.
    |
    */

    'capabilities' => [
        [
            'title' => 'Full Stack Applications',
            'description' => 'Complete products from interface design through API design, database modelling, authentication and deployment.',
        ],
        [
            'title' => 'AI-Powered Applications',
            'description' => 'Python and FastAPI services wrapped around vision and retrieval models, integrated into Laravel backends.',
        ],
        [
            'title' => 'Backend & API Systems',
            'description' => 'Laravel applications, REST APIs, admin panels and third-party integrations with clean, testable boundaries.',
        ],
        [
            'title' => 'Deployment & Infrastructure',
            'description' => 'Linux server setup, Nginx configuration, process supervision and the deployment pipelines that keep it running.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Tech stack
    |--------------------------------------------------------------------------
    |
    | These are the technologies I work with directly. There are no proficiency
    | percentages here on purpose - a badge means "used in shipped work", never
    | "98% skilled". The `skills` table is rendered underneath this list, so the
    | page can only ever show technology backed by real content.
    |
    */

    'stack_groups' => [
        [
            'label' => 'Frontend',
            'items' => ['Vue', 'TypeScript', 'JavaScript', 'Tailwind CSS', 'Vite', 'Alpine.js'],
        ],
        [
            'label' => 'Backend',
            'items' => ['PHP', 'Laravel', 'REST API', 'Blade'],
        ],
        [
            'label' => 'AI / Machine Learning',
            'items' => ['Python', 'FastAPI', 'SigLIP2', 'DINOv2', 'FAISS'],
        ],
        [
            'label' => 'Database',
            'items' => ['MySQL', 'PostgreSQL'],
        ],
        [
            'label' => 'Infrastructure',
            'items' => ['Linux', 'Nginx', 'Git', 'GitHub Actions'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Project types
    |--------------------------------------------------------------------------
    |
    | A short, controlled vocabulary so the admin form can validate instead of
    | accepting free text. 'Other' always remains available as a fallback.
    |
    */

    'project_types' => [
        'Full Stack Application',
        'AI-Powered Application',
        'Backend & API',
        'Deployment & Infrastructure',
        'Other',
    ],

    /*
    |--------------------------------------------------------------------------
    | How I work
    |--------------------------------------------------------------------------
    */

    'working_principles' => [
        [
            'title' => 'Understand the problem first',
            'description' => 'Requirements and data shape before scaffolding. Most rework comes from building the wrong thing accurately.',
        ],
        [
            'title' => 'Ship the whole product',
            'description' => 'Interface, backend, deployment and monitoring. Handing over a half-built feature is not shipping.',
        ],
        [
            'title' => 'Keep the boundaries explicit',
            'description' => 'Clear contracts between the Laravel application, the Python AI service and the database.',
        ],
        [
            'title' => 'Optimise for the next reader',
            'description' => 'Consistent naming, small focused classes, and code that a new developer can follow without a walkthrough.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Development journey
    |--------------------------------------------------------------------------
    |
    | Deliberately empty. Employment history, dates and roles are factual claims,
    | so they are only ever added here once verified - never generated to fill the
    | section. The timeline section hides itself while this list is empty.
    |
    */

    'journey' => [
        // [
        //     'period' => '2022 — Present',
        //     'title' => 'Full Stack Developer',
        //     'organisation' => '',
        //     'description' => '',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | SEO defaults
    |--------------------------------------------------------------------------
    */

    'seo' => [
        'title' => 'Satria Rangga Jati — Full Stack Developer, AI-Powered Web Applications',
        'og_image' => 'img/og-default.png',
        'twitter_site' => env('PORTFOLIO_TWITTER_SITE'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Contact form
    |--------------------------------------------------------------------------
    |
    | Messages are stored in the `contact_messages` table. No third-party
    | credentials are required.
    |
    */

    'contact_form' => [
        'max_per_minute' => 5,
        'max_message_length' => 4000,
    ],

];
