<?php
/**
 * Demo data generator for testing and showcasing the plugin.
 *
 * @package Peanut_Booker
 * @since   1.1.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

// Load demo data creators trait.
require_once __DIR__ . '/demo-data/trait-creators.php';

/**
 * Demo Data Generator Class.
 */
class Peanut_Booker_Demo_Data {

    use Peanut_Booker_Demo_Creators;

    /**
     * Initialize demo mode hooks.
     */
    public static function init() {
        if ( self::is_demo_mode() ) {
            // Add demo banner to frontend.
            add_action( 'wp_head', array( __CLASS__, 'add_demo_banner_styles' ) );
            add_action( 'wp_body_open', array( __CLASS__, 'render_frontend_banner' ) );
            add_action( 'wp_footer', array( __CLASS__, 'render_frontend_banner_fallback' ) );

            // Add demo banner to admin.
            add_action( 'admin_head', array( __CLASS__, 'add_demo_banner_styles' ) );
            add_action( 'admin_notices', array( __CLASS__, 'render_admin_banner' ) );

            // Add body class.
            add_filter( 'body_class', array( __CLASS__, 'add_demo_body_class' ) );
            add_filter( 'admin_body_class', array( __CLASS__, 'add_demo_admin_body_class' ) );
        }
    }

    /**
     * Demo performer data with varied achievement levels.
     *
     * @var array
     */
    private static $performers = array(
        // PLATINUM PERFORMER
        array(
            'name'              => 'Marcus "The Magnificent" Johnson',
            'email'             => 'marcus.magnificent@demo.peanutbooker.test',
            'category'          => 'Magicians',
            'tagline'           => 'Mind-bending illusions that will leave your guests speechless',
            'bio'               => "With over 15 years of experience performing at corporate events, private parties, and Las Vegas residencies, Marcus Johnson brings world-class magic to every performance.\n\nSpecializing in close-up magic, mentalism, and grand illusions, Marcus tailors each show to your specific event needs. His interactive style ensures every guest feels part of the magic.\n\nPast clients include Fortune 500 companies, celebrity weddings, and sold-out theater shows. Marcus has been featured on America's Got Talent, Penn & Teller: Fool Us, and numerous late-night talk shows.\n\nWhether you need a roaming performer for cocktail hour or a full stage show, Marcus delivers unforgettable entertainment.",
            'hourly_rate'       => 350,
            'tier'              => 'pro',
            'city'              => 'Las Vegas',
            'state'             => 'NV',
            'experience'        => 15,
            'verified'          => true,
            'featured'          => true,
            'achievement_level' => 'platinum',
            'completed_bookings'=> 250,
            'avg_rating'        => 4.9,
            'total_reviews'     => 185,
        ),
        // GOLD PERFORMER
        array(
            'name'              => 'Sarah Chen',
            'email'             => 'sarah.chen@demo.peanutbooker.test',
            'category'          => 'Musicians',
            'tagline'           => 'Classical violin with a modern twist',
            'bio'               => "Classically trained at Juilliard, Sarah brings elegance and sophistication to any event. From intimate dinner parties to grand weddings, her repertoire spans centuries of beautiful music.\n\nSarah performs solo or with her string quartet, offering everything from Bach to contemporary pop arrangements. She's particularly known for her emotional wedding ceremony performances.\n\nWith a degree from one of the world's premier music conservatories and 12 years of professional experience, Sarah has performed at Carnegie Hall, the Kennedy Center, and countless private events for distinguished clients.\n\nAvailable for ceremonies, cocktail hours, receptions, and corporate events throughout the tri-state area.",
            'hourly_rate'       => 225,
            'tier'              => 'pro',
            'city'              => 'New York',
            'state'             => 'NY',
            'experience'        => 12,
            'verified'          => true,
            'featured'          => true,
            'achievement_level' => 'gold',
            'completed_bookings'=> 120,
            'avg_rating'        => 4.8,
            'total_reviews'     => 95,
        ),
        // GOLD PERFORMER
        array(
            'name'              => 'DJ Blaze',
            'email'             => 'dj.blaze@demo.peanutbooker.test',
            'category'          => 'DJs',
            'tagline'           => 'Keeping the party going all night long',
            'bio'               => "DJ Blaze has been rocking parties across the country for 8 years. Specializing in weddings, corporate events, and private parties, he knows exactly how to read a crowd and keep the energy high.\n\nFull sound system and lighting included with every booking. Music library spans all genres from the 60s to today's hottest hits. Professional-grade equipment ensures crystal-clear sound for venues of any size.\n\nMC services available for announcements and special moments. Bilingual capabilities (English/Spanish) for diverse celebrations.\n\nFeatured DJ at Ultra Music Festival and guest appearances at major clubs in Miami, New York, and Las Vegas.",
            'hourly_rate'       => 175,
            'tier'              => 'pro',
            'city'              => 'Miami',
            'state'             => 'FL',
            'experience'        => 8,
            'verified'          => true,
            'featured'          => false,
            'achievement_level' => 'gold',
            'completed_bookings'=> 95,
            'avg_rating'        => 4.7,
            'total_reviews'     => 78,
        ),
        // PLATINUM PERFORMER
        array(
            'name'              => 'Tommy "Two-Shoes" Martinez',
            'email'             => 'tommy.comedy@demo.peanutbooker.test',
            'category'          => 'Comedians',
            'tagline'           => 'Clean comedy that gets everyone laughing',
            'bio'               => "Tommy Martinez brings the laughs without the awkward moments. His family-friendly comedy style makes him perfect for corporate events, fundraisers, and celebrations where grandma is in attendance.\n\nFeatured on Comedy Central, Netflix's \"The Comedy Lineup\", and NBC's \"Last Comic Standing.\" Tommy has headlined clubs across America and performed at over 500 corporate events.\n\nHis observational humor and quick wit connect with audiences of all ages and backgrounds. Tommy can tailor his set to your specific audience and event.\n\nCustom material available for roasts, award ceremonies, and special corporate messaging. References available from Fortune 500 clients.",
            'hourly_rate'       => 400,
            'tier'              => 'pro',
            'city'              => 'Chicago',
            'state'             => 'IL',
            'experience'        => 10,
            'verified'          => true,
            'featured'          => true,
            'achievement_level' => 'platinum',
            'completed_bookings'=> 310,
            'avg_rating'        => 4.9,
            'total_reviews'     => 245,
        ),
        // GOLD PERFORMER
        array(
            'name'              => 'Elena Rodriguez',
            'email'             => 'elena.speaks@demo.peanutbooker.test',
            'category'          => 'Speakers',
            'tagline'           => 'Inspiring teams to reach their full potential',
            'bio'               => "Former Fortune 100 executive turned motivational speaker, Elena Rodriguez delivers powerful keynotes on leadership, diversity, and workplace excellence.\n\nHer TEDx talk \"Breaking the Glass Ceiling Without Breaking Yourself\" has over 2 million views, and she's authored two bestselling books on professional development.\n\nElena customizes each presentation to align with your organization's goals and challenges. Her interactive workshops leave participants with actionable strategies they can implement immediately.\n\nTopics include: Leadership in Crisis, Building Inclusive Teams, Women in Business, The Future of Work, and Resilience in the Modern Workplace.",
            'hourly_rate'       => 750,
            'tier'              => 'pro',
            'city'              => 'Austin',
            'state'             => 'TX',
            'experience'        => 20,
            'verified'          => true,
            'featured'          => true,
            'achievement_level' => 'gold',
            'completed_bookings'=> 85,
            'avg_rating'        => 5.0,
            'total_reviews'     => 72,
        ),
        // SILVER PERFORMER
        array(
            'name'              => 'The Groove Collective',
            'email'             => 'groove.collective@demo.peanutbooker.test',
            'category'          => 'Musicians',
            'tagline'           => '7-piece band bringing the funk to your event',
            'bio'               => "The Groove Collective is a high-energy 7-piece band specializing in funk, soul, Motown, and contemporary hits. With a full horn section, we bring the sound of a stadium concert to your event.\n\nPerfect for weddings, corporate galas, and any event where you want people on their feet dancing. Our extensive setlist covers 60+ years of dance floor favorites from James Brown to Bruno Mars.\n\nFull production available including professional sound, intelligent lighting, and stage setup. We've performed at venues ranging from intimate 50-person parties to 5,000-seat arenas.\n\nEach member has 10+ years of professional experience and has toured with major recording artists.",
            'hourly_rate'       => 500,
            'tier'              => 'pro',
            'city'              => 'Atlanta',
            'state'             => 'GA',
            'experience'        => 15,
            'verified'          => true,
            'featured'          => false,
            'achievement_level' => 'silver',
            'completed_bookings'=> 45,
            'avg_rating'        => 4.8,
            'total_reviews'     => 38,
        ),
        // BRONZE PERFORMER (Free tier)
        array(
            'name'              => 'Amazing Andy',
            'email'             => 'amazing.andy@demo.peanutbooker.test',
            'category'          => 'Variety Acts',
            'tagline'           => 'Juggling, unicycle, and comedy combined',
            'bio'               => "Part comedian, part circus performer, Andy brings a unique blend of physical comedy, juggling, and stunts that amazes audiences of all ages.\n\nGreat for family events, fairs, festivals, and company picnics. His interactive show gets kids and adults alike involved in the fun.\n\nAndy's signature act features juggling flaming torches while riding a 6-foot unicycle! He also performs balloon artistry and stilt walking.\n\nCan perform indoors or outdoors, shows range from 30-60 minutes. Self-contained with portable sound system.",
            'hourly_rate'       => 125,
            'tier'              => 'free',
            'city'              => 'Portland',
            'state'             => 'OR',
            'experience'        => 6,
            'verified'          => false,
            'featured'          => false,
            'achievement_level' => 'bronze',
            'completed_bookings'=> 18,
            'avg_rating'        => 4.5,
            'total_reviews'     => 12,
        ),
        // SILVER PERFORMER
        array(
            'name'              => 'Luna Dance Company',
            'email'             => 'luna.dance@demo.peanutbooker.test',
            'category'          => 'Dancers',
            'tagline'           => 'Elegant performances for unforgettable events',
            'bio'               => "Luna Dance Company offers professional dance performances including ballet, contemporary, ballroom, Latin, and cultural dances from around the world.\n\nOur dancers have performed with major ballet companies including ABT and Pacific Northwest Ballet, as well as on Broadway and with major recording artists' tours.\n\nWe create custom choreography to match your event theme. From a single elegant dancer to a full corps of 12, we scale our performances to your venue and vision.\n\nServices include: Featured performances, first dance choreography and lessons, flash mobs, dance instruction, and themed entertainment packages.",
            'hourly_rate'       => 275,
            'tier'              => 'pro',
            'city'              => 'Los Angeles',
            'state'             => 'CA',
            'experience'        => 10,
            'verified'          => true,
            'featured'          => false,
            'achievement_level' => 'silver',
            'completed_bookings'=> 52,
            'avg_rating'        => 4.6,
            'total_reviews'     => 41,
        ),
        // GOLD PERFORMER
        array(
            'name'              => 'Mike the Mentalist',
            'email'             => 'mike.mentalist@demo.peanutbooker.test',
            'category'          => 'Magicians',
            'tagline'           => 'Reading minds and blowing them',
            'bio'               => "Mike specializes in mentalism and psychological illusions. His corporate shows explore the fascinating world of human psychology through mind-reading demonstrations that will have your guests questioning reality.\n\nPerfect for conferences, team-building events, and upscale private parties. His sophisticated approach appeals to skeptics and believers alike.\n\nFeatured performer at corporate events for Google, Apple, Microsoft, and numerous Fortune 500 companies. Mike's blend of psychology, magic, and humor creates a unique experience.\n\nCustom experiences available for product launches and brand activations where the \"magic\" ties into your messaging.",
            'hourly_rate'       => 425,
            'tier'              => 'pro',
            'city'              => 'San Francisco',
            'state'             => 'CA',
            'experience'        => 12,
            'verified'          => true,
            'featured'          => true,
            'achievement_level' => 'gold',
            'completed_bookings'=> 78,
            'avg_rating'        => 4.9,
            'total_reviews'     => 65,
        ),
        // BRONZE PERFORMER (Free tier)
        array(
            'name'              => 'Acoustic Amy',
            'email'             => 'acoustic.amy@demo.peanutbooker.test',
            'category'          => 'Musicians',
            'tagline'           => 'Soulful acoustic covers for intimate gatherings',
            'bio'               => "Amy performs acoustic covers of popular songs spanning multiple decades and genres. Her warm voice and skilled guitar playing create the perfect ambiance for restaurants, wineries, and intimate events.\n\nShe takes requests and can learn specific songs for your special day with advance notice. Great for cocktail hours, dinner music, and small celebrations.\n\nCurrently building her performance portfolio and offering competitive rates. Originally from Nashville, Amy brings genuine Music City talent to every show.\n\nAlso available for recording sessions and jingles.",
            'hourly_rate'       => 100,
            'tier'              => 'free',
            'city'              => 'Nashville',
            'state'             => 'TN',
            'experience'        => 3,
            'verified'          => false,
            'featured'          => false,
            'achievement_level' => 'bronze',
            'completed_bookings'=> 8,
            'avg_rating'        => 4.4,
            'total_reviews'     => 6,
        ),
        // SILVER PERFORMER
        array(
            'name'              => 'The String Theory Quartet',
            'email'             => 'stringtheory@demo.peanutbooker.test',
            'category'          => 'Musicians',
            'tagline'           => 'Classical elegance meets modern hits',
            'bio'               => "The String Theory Quartet brings a fresh approach to string ensemble performance. Our repertoire seamlessly blends classical masterpieces with arrangements of contemporary pop, rock, and indie favorites.\n\nImagine walking down the aisle to a beautiful string arrangement of your favorite song, or cocktail hour featuring tasteful versions of Coldplay, Ed Sheeran, and Adele.\n\nAll four members hold music degrees from top conservatories and have performed with major symphony orchestras. We specialize in weddings, corporate events, and private celebrations.\n\nCustom song arrangements available with 4 weeks notice. Professional attire appropriate to your event's dress code.",
            'hourly_rate'       => 350,
            'tier'              => 'pro',
            'city'              => 'Boston',
            'state'             => 'MA',
            'experience'        => 8,
            'verified'          => true,
            'featured'          => false,
            'achievement_level' => 'silver',
            'completed_bookings'=> 62,
            'avg_rating'        => 4.7,
            'total_reviews'     => 48,
        ),
        // BRONZE PERFORMER
        array(
            'name'              => 'Carlos the Caricaturist',
            'email'             => 'carlos.art@demo.peanutbooker.test',
            'category'          => 'Variety Acts',
            'tagline'           => 'Quick-sketch portraits that capture the fun',
            'bio'               => "Carlos creates fun, flattering caricature portraits in just 3-5 minutes per person. Perfect for trade shows, corporate events, bar mitzvahs, weddings, and any celebration where you want guests to take home a unique souvenir.\n\nEach guest receives their portrait on quality paper, ready to frame. Digital versions also available for social media sharing.\n\nWith a fine arts background and 5 years of event caricature experience, Carlos captures likeness and personality with humor and skill. He's great with kids and adults alike.\n\nAll supplies included. Can accommodate 10-15 guests per hour depending on complexity.",
            'hourly_rate'       => 150,
            'tier'              => 'free',
            'city'              => 'Phoenix',
            'state'             => 'AZ',
            'experience'        => 5,
            'verified'          => true,
            'featured'          => false,
            'achievement_level' => 'bronze',
            'completed_bookings'=> 24,
            'avg_rating'        => 4.6,
            'total_reviews'     => 18,
        ),
    );

    /**
     * Demo customer data.
     *
     * @var array
     */
    private static $customers = array(
        array(
            'name'    => 'Jennifer Thompson',
            'email'   => 'jennifer.thompson@demo.peanutbooker.test',
            'company' => '',
        ),
        array(
            'name'    => 'Robert Chen',
            'email'   => 'robert.chen@demo.peanutbooker.test',
            'company' => '',
        ),
        array(
            'name'    => 'Amanda Williams',
            'email'   => 'amanda.williams@demo.peanutbooker.test',
            'company' => '',
        ),
        array(
            'name'    => 'Michael Davis',
            'email'   => 'michael.davis@demo.peanutbooker.test',
            'company' => 'Davis Event Planning',
        ),
        array(
            'name'    => 'Sarah Mitchell',
            'email'   => 'sarah.mitchell@demo.peanutbooker.test',
            'company' => 'Mitchell Wedding Coordination',
        ),
        array(
            'name'    => 'David Anderson',
            'email'   => 'david.anderson@demo.peanutbooker.test',
            'company' => '',
        ),
        array(
            'name'    => 'Lisa Martinez',
            'email'   => 'lisa.martinez@demo.peanutbooker.test',
            'company' => 'Martinez Productions',
        ),
        array(
            'name'    => 'Corporate Events Inc.',
            'email'   => 'events@corporatedemo.peanutbooker.test',
            'company' => 'Corporate Events Inc.',
        ),
        array(
            'name'    => 'Tech Conference Group',
            'email'   => 'booking@techconf.peanutbooker.test',
            'company' => 'TechConf LLC',
        ),
        array(
            'name'    => 'Wedding Wishes LLC',
            'email'   => 'info@weddingwishes.peanutbooker.test',
            'company' => 'Wedding Wishes LLC',
        ),
    );

    /**
     * Demo review content templates.
     *
     * @var array
     */
    private static $review_templates = array(
        5 => array(
            array(
                'title'    => 'Absolutely incredible!',
                'content'  => '{name} exceeded all our expectations. Our guests are still talking about the performance weeks later. Professional from start to finish, arrived early, and delivered a show that had everyone captivated. Highly recommend!',
                'response' => 'Thank you so much for these kind words! It was truly a pleasure performing at your event. Your guests were wonderful and made my job easy. I hope to work with you again in the future!',
            ),
            array(
                'title'    => 'Best decision we made for our wedding',
                'content'  => 'We\'ve hired many performers over the years, but {name} was by far the best. The attention to detail, communication leading up to the event, and the actual performance were all exceptional. Worth every penny and then some!',
                'response' => 'What an honor to be part of your special day! Weddings are my favorite events to perform at, and yours was truly magical. Wishing you both a lifetime of happiness!',
            ),
            array(
                'title'    => 'Made our corporate event unforgettable',
                'content'  => 'From the first inquiry to the final bow, everything was perfect. {name} understood our corporate culture and tailored the performance accordingly. Our team is still raving about it. Already booked for next year!',
                'response' => 'It was a pleasure working with your team! I really enjoyed getting to know your company culture and crafting a performance that resonated. Looking forward to next year!',
            ),
            array(
                'title'    => 'Five stars isn\'t enough!',
                'content'  => '{name} had the entire crowd engaged and entertained from the first minute to the last. The highlight of our event without question. Multiple guests asked for contact info. A true professional!',
                'response' => 'You\'re too kind! Your event was fantastic to be a part of - great venue, great crowd, great energy. Thank you for having me!',
            ),
            array(
                'title'    => 'Exceeded every expectation',
                'content'  => 'We had high hopes based on the reviews, and {name} somehow exceeded them all. The performance was flawless, the interaction with guests was perfect, and the value was outstanding. Could not be happier!',
                'response' => null,
            ),
        ),
        4 => array(
            array(
                'title'    => 'Great performance, would book again',
                'content'  => 'Great performance by {name}! Very professional and entertaining. The only minor issue was arrival time (about 15 minutes late), but once the show started it was fantastic. Would definitely recommend.',
                'response' => 'Thank you for the feedback! I apologize for the delay - traffic was unexpected that day. I\'m glad the performance itself met your expectations. I always aim for punctuality and will plan better next time!',
            ),
            array(
                'title'    => 'Solid performance, happy guests',
                'content'  => '{name} did a wonderful job at our event. Guests loved it! Would have been perfect with a bit more variety in the set, but overall excellent entertainment. Good communication throughout.',
                'response' => 'Thanks for the review! I appreciate the feedback about variety - I\'ll keep that in mind for future performances. Glad your guests had a great time!',
            ),
            array(
                'title'    => 'Professional and talented',
                'content'  => 'Really enjoyed having {name} perform. Good communication before the event and a solid show. A few minor technical hiccups but handled professionally. Would recommend to others looking for quality entertainment.',
                'response' => null,
            ),
            array(
                'title'    => 'Great entertainment value',
                'content'  => '{name} provided exactly what we needed for our event. Professional, entertaining, and easy to work with. The performance was engaging and guests were happy. Would book again for future events.',
                'response' => 'Thank you! It was a pleasure performing at your event. Hope to see you again!',
            ),
        ),
        3 => array(
            array(
                'title'    => 'Decent performance, met expectations',
                'content'  => '{name} was decent. The performance was good but not quite what we expected based on the description. Still, guests seemed to enjoy it and there were no issues. Fair value for the price.',
                'response' => 'I appreciate your honest feedback. I\'d love to discuss what could have been different to better meet your expectations. Every performance is a learning opportunity. Thank you for having me.',
            ),
            array(
                'title'    => 'Average experience overall',
                'content'  => 'Average experience with {name}. Professional but nothing extraordinary. Met our basic needs for the event but didn\'t quite wow the crowd as we\'d hoped. Communication was good at least.',
                'response' => null,
            ),
        ),
    );

    /**
     * Demo market event templates.
     *
     * @var array
     */
    private static $event_templates = array(
        array(
            'name'        => 'Corporate Holiday Party Entertainment',
            'description' => "We're hosting our annual company holiday party for 150 employees and need engaging entertainment. Looking for someone who can perform for about 2 hours during our dinner and awards ceremony.\n\nFamily-friendly content required as employees may bring spouses and older children. Venue has a stage and full sound system available.\n\nPreferred: Interactive elements that get employees involved, ability to incorporate some company-specific humor if provided, professional appearance.",
            'category'    => 'Variety Acts',
            'budget_min'  => 500,
            'budget_max'  => 1000,
            'duration'    => 2,
            'status'      => 'open',
        ),
        array(
            'name'        => 'Wedding Reception DJ - Beach Venue',
            'description' => "Getting married on the beach! Need a DJ who can handle outdoor setup and keep 100 guests dancing until midnight. Must have own equipment suitable for outdoor/beach use.\n\nLooking for someone experienced with wedding formats - announcements, first dance, parent dances, cake cutting, bouquet toss, etc. We have a detailed timeline to follow.\n\nMusic style: Mix of current hits, 80s/90s classics, and some Latin music. Open to creative song suggestions for special moments!\n\nPlease include your equipment specs and backup plan for weather issues.",
            'category'    => 'DJs',
            'budget_min'  => 800,
            'budget_max'  => 1500,
            'duration'    => 5,
            'status'      => 'open',
        ),
        array(
            'name'        => 'Kids Birthday Party Magician',
            'description' => "My daughter is turning 7 and is obsessed with magic! Looking for a magician who specializes in children's entertainment.\n\nParty will have about 20 kids ages 5-10 in our backyard (covered patio area available). Hoping for an interactive show where kids can participate - maybe learn a trick or two?\n\nPlease let us know if you offer any package deals that include balloon animals or face painting!",
            'category'    => 'Magicians',
            'budget_min'  => 200,
            'budget_max'  => 400,
            'duration'    => 1,
            'status'      => 'open',
        ),
        array(
            'name'        => 'Tech Conference Keynote Speaker',
            'description' => "Annual tech conference seeking inspiring keynote speaker for opening session. Topic should relate to innovation, leadership, AI/technology trends, or the future of work.\n\nAudience: 500+ tech professionals, executives, and entrepreneurs. 45-minute presentation with 15-minute Q&A.\n\nWe're looking for someone who can energize the room and set an inspiring tone for the 3-day conference. Previous conference speaking experience required.\n\nPremium compensation for the right speaker. Please include links to previous speaking engagements.",
            'category'    => 'Speakers',
            'budget_min'  => 3000,
            'budget_max'  => 7500,
            'duration'    => 1,
            'status'      => 'open',
        ),
        array(
            'name'        => 'Live Band for Charity Gala',
            'description' => "Upscale charity gala needs a versatile band that can provide background music during dinner and get people on the dance floor afterward. Black-tie event at the city art museum.\n\nLooking for a band with a diverse repertoire - jazz standards during dinner transitioning to Motown, soul, and current hits for dancing.\n\nEvent timeline:\n- 6:00 PM: Cocktail hour (solo pianist or small ensemble)\n- 7:00 PM: Dinner (soft background music)\n- 9:00 PM: Dancing (full band energy)\n- 11:00 PM: Event ends\n\nMust be able to accommodate song requests from major donors.",
            'category'    => 'Musicians',
            'budget_min'  => 2000,
            'budget_max'  => 4000,
            'duration'    => 5,
            'status'      => 'open',
        ),
        array(
            'name'        => 'Comedy Night Headliner',
            'description' => "Our venue hosts monthly comedy nights and we're looking for a headliner for our upcoming show. 21+ crowd of about 150 people, craft beer and cocktail venue.\n\nEdgier material welcome but nothing too controversial - we want everyone to have a good time. 45-minute set.\n\nWe provide sound and staging. Good exposure opportunity - we film all shows for our YouTube channel (8K subscribers) with performer permission.\n\nOpener already booked. Looking specifically for the headliner spot.",
            'category'    => 'Comedians',
            'budget_min'  => 500,
            'budget_max'  => 1000,
            'duration'    => 1,
            'status'      => 'open',
        ),
        array(
            'name'        => 'First Dance Choreography for Wedding',
            'description' => "We're getting married in 3 months and want to surprise our guests with a choreographed first dance! We're both complete beginners - last time I danced was probably prom.\n\nOur song is \"Perfect\" by Ed Sheeran (about 4 minutes). Looking for someone to teach us a routine that looks impressive but is achievable for non-dancers.\n\nWe can practice 2-3 times per week at your studio or our location. Please include your hourly rate and estimate of how many lessons we'll need.\n\nBonus if you can also help with the parent dances!",
            'category'    => 'Dancers',
            'budget_min'  => 300,
            'budget_max'  => 800,
            'duration'    => 10,
            'status'      => 'open',
        ),
        array(
            'name'        => 'Company Anniversary Celebration Entertainment',
            'description' => "Our company is celebrating 25 years in business! We're throwing a party for 200 employees, clients, and partners at a downtown event venue.\n\nLooking for sophisticated entertainment that can work the room during cocktails (first 2 hours) and then transition to something more engaging/interactive for the main event portion.\n\nWe're open to creative ideas! Possibilities: magician, mentalist, live artist, unique variety act. What makes you stand out?\n\nNote: Some speeches and a video presentation will happen, so performer needs to work around that schedule.",
            'category'    => 'Variety Acts',
            'budget_min'  => 800,
            'budget_max'  => 1500,
            'duration'    => 4,
            'status'      => 'open',
        ),
        // Some closed/filled events
        array(
            'name'        => 'New Year\'s Eve Gala - DJ Needed',
            'description' => "PERFORMER FOUND - Thank you to all who submitted bids!\n\nUpscale NYE party needs experienced DJ. 200 guests, open bar, dance floor focus. Countdown at midnight with special effects. Full production required.",
            'category'    => 'DJs',
            'budget_min'  => 1500,
            'budget_max'  => 2500,
            'duration'    => 6,
            'status'      => 'closed',
        ),
        array(
            'name'        => 'Corporate Leadership Summit - Motivational Speaker',
            'description' => "Looking for dynamic speaker on leadership and team building for 2-day summit. 75 executives attending.\n\nEvent was successful - performer exceeded expectations!",
            'category'    => 'Speakers',
            'budget_min'  => 5000,
            'budget_max'  => 10000,
            'duration'    => 2,
            'status'      => 'filled',
        ),
    );

    /**
     * Check if demo mode is enabled.
     *
     * @return bool
     */
    public static function is_demo_mode() {
        return (bool) get_option( 'peanut_booker_demo_mode', false );
    }

    /**
     * Add demo body class.
     *
     * @param array $classes Body classes.
     * @return array Modified classes.
     */
    public static function add_demo_body_class( $classes ) {
        $classes[] = 'pb-demo-mode-active';
        return $classes;
    }

    /**
     * Add demo admin body class.
     *
     * @param string $classes Body classes.
     * @return string Modified classes.
     */
    public static function add_demo_admin_body_class( $classes ) {
        return $classes . ' pb-demo-mode-active';
    }

    /**
     * Add demo banner styles.
     */
    public static function add_demo_banner_styles() {
        ?>
        <style>
            /* Demo Mode Banner */
            .pb-demo-banner {
                background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #a855f7 100%);
                color: #fff;
                padding: 12px 20px;
                text-align: center;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                font-size: 14px;
                font-weight: 600;
                position: relative;
                z-index: 99999;
                box-shadow: 0 2px 10px rgba(99, 102, 241, 0.3);
            }
            .pb-demo-banner::before {
                content: "";
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: repeating-linear-gradient(
                    45deg,
                    transparent,
                    transparent 10px,
                    rgba(255,255,255,0.03) 10px,
                    rgba(255,255,255,0.03) 20px
                );
            }
            .pb-demo-banner-content {
                position: relative;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 15px;
                flex-wrap: wrap;
            }
            .pb-demo-banner-badge {
                background: rgba(255,255,255,0.2);
                padding: 4px 12px;
                border-radius: 20px;
                font-weight: 700;
                letter-spacing: 1px;
                text-transform: uppercase;
                font-size: 11px;
                border: 1px solid rgba(255,255,255,0.3);
            }
            .pb-demo-banner-text {
                opacity: 0.95;
            }
            .pb-demo-banner a {
                color: #fff;
                text-decoration: underline;
                opacity: 0.9;
            }
            .pb-demo-banner a:hover {
                opacity: 1;
            }

            /* Frontend specific */
            body.pb-demo-mode-active {
                padding-top: 0 !important;
            }

            /* Admin specific - adjust for admin bar */
            .wp-admin .pb-demo-banner {
                margin: -1px -1px 20px -20px;
                width: calc(100% + 21px);
            }
            @media screen and (max-width: 782px) {
                .wp-admin .pb-demo-banner {
                    margin-left: -10px;
                    width: calc(100% + 11px);
                }
            }

            /* Pulse animation for the badge */
            @keyframes pb-pulse {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.05); }
            }
            .pb-demo-banner-badge {
                animation: pb-pulse 2s ease-in-out infinite;
            }
        </style>
        <?php
    }

    /**
     * Render frontend demo banner.
     */
    public static function render_frontend_banner() {
        if ( is_admin() ) {
            return;
        }
        self::render_banner( false );
        // Flag that banner was rendered.
        $GLOBALS['pb_demo_banner_rendered'] = true;
    }

    /**
     * Render frontend banner fallback (for themes without wp_body_open).
     */
    public static function render_frontend_banner_fallback() {
        if ( is_admin() || ! empty( $GLOBALS['pb_demo_banner_rendered'] ) ) {
            return;
        }
        echo '<script>
            (function() {
                if (!document.querySelector(".pb-demo-banner")) {
                    var banner = document.createElement("div");
                    banner.className = "pb-demo-banner";
                    banner.innerHTML = \'<div class="pb-demo-banner-content"><span class="pb-demo-banner-badge">DEMO MODE</span><span class="pb-demo-banner-text">This site is running in demonstration mode with sample data.</span></div>\';
                    document.body.insertBefore(banner, document.body.firstChild);
                }
            })();
        </script>';
    }

    /**
     * Render admin demo banner.
     */
    public static function render_admin_banner() {
        self::render_banner( true );
    }

    /**
     * Render the banner.
     *
     * @param bool $is_admin Whether this is the admin area.
     */
    private static function render_banner( $is_admin = false ) {
        $manage_url = admin_url( 'admin.php?page=pb-demo' );
        ?>
        <div class="pb-demo-banner">
            <div class="pb-demo-banner-content">
                <span class="pb-demo-banner-badge">DEMO MODE</span>
                <span class="pb-demo-banner-text">
                    This site is running in demonstration mode with sample data.
                    <?php if ( $is_admin && current_user_can( 'manage_options' ) ) : ?>
                        <a href="<?php echo esc_url( $manage_url ); ?>">Manage Demo Mode</a>
                    <?php endif; ?>
                </span>
            </div>
        </div>
        <?php
    }

    /**
     * Enable demo mode and generate data.
     *
     * @return array Result with counts of created items.
     */
    public static function enable_demo_mode() {
        // Check if already enabled.
        if ( self::is_demo_mode() ) {
            return array( 'error' => __( 'Demo mode is already enabled.', 'peanut-booker' ) );
        }

        $results = array(
            'performers'   => 0,
            'customers'    => 0,
            'bookings'     => 0,
            'reviews'      => 0,
            'events'       => 0,
            'bids'         => 0,
            'transactions' => 0,
            'availability' => 0,
            'microsites'   => 0,
        );

        // Create categories first.
        self::create_demo_categories();

        // Create performers.
        $performer_ids = self::create_demo_performers();
        $results['performers'] = count( $performer_ids );

        // Create microsites for performers.
        $results['microsites'] = self::create_demo_microsites( $performer_ids );

        // Create customers.
        $customer_ids = self::create_demo_customers();
        $results['customers'] = count( $customer_ids );

        // Create bookings, reviews, and transactions.
        $booking_results = self::create_demo_bookings( $performer_ids, $customer_ids );
        $results['bookings']     = $booking_results['bookings'];
        $results['reviews']      = $booking_results['reviews'];
        $results['transactions'] = $booking_results['transactions'];

        // Create market events and bids.
        $market_results = self::create_demo_market_events( $performer_ids, $customer_ids );
        $results['events'] = $market_results['events'];
        $results['bids']   = $market_results['bids'];

        // Mark demo mode as enabled.
        update_option( 'peanut_booker_demo_mode', true );
        update_option( 'peanut_booker_demo_data_ids', array(
            'performer_user_ids' => $performer_ids,
            'customer_user_ids'  => $customer_ids,
        ) );

        return $results;
    }

    /**
     * Disable demo mode and remove data.
     *
     * @return bool
     */
    public static function disable_demo_mode() {
        if ( ! self::is_demo_mode() ) {
            return false;
        }

        $demo_ids = get_option( 'peanut_booker_demo_data_ids', array() );

        // Remove demo users and their data.
        $performer_user_ids = $demo_ids['performer_user_ids'] ?? array();
        $customer_user_ids  = $demo_ids['customer_user_ids'] ?? array();
        $all_user_ids       = array_merge( $performer_user_ids, $customer_user_ids );

        // Delete performer profile posts first.
        foreach ( $performer_user_ids as $user_id ) {
            $performer = Peanut_Booker_Database::get_row( 'performers', array( 'user_id' => $user_id ) );
            if ( $performer && $performer->profile_id ) {
                wp_delete_post( $performer->profile_id, true );
            }
        }

        // Delete users.
        require_once ABSPATH . 'wp-admin/includes/user.php';
        foreach ( $all_user_ids as $user_id ) {
            wp_delete_user( $user_id );
        }

        // Clean up demo data from custom tables.
        global $wpdb;

        if ( ! empty( $performer_user_ids ) ) {
            $perf_placeholders = implode( ',', array_fill( 0, count( $performer_user_ids ), '%d' ) );

            // Get performer IDs for related cleanup.
            $performer_table_ids = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT id FROM {$wpdb->prefix}pb_performers WHERE user_id IN ($perf_placeholders)",
                    $performer_user_ids
                )
            );

            if ( ! empty( $performer_table_ids ) ) {
                $perf_id_placeholders = implode( ',', array_fill( 0, count( $performer_table_ids ), '%d' ) );

                // Delete availability.
                $wpdb->query(
                    $wpdb->prepare(
                        "DELETE FROM {$wpdb->prefix}pb_availability WHERE performer_id IN ($perf_id_placeholders)",
                        $performer_table_ids
                    )
                );

                // Delete bookings.
                $wpdb->query(
                    $wpdb->prepare(
                        "DELETE FROM {$wpdb->prefix}pb_bookings WHERE performer_id IN ($perf_id_placeholders)",
                        $performer_table_ids
                    )
                );

                // Delete bids.
                $wpdb->query(
                    $wpdb->prepare(
                        "DELETE FROM {$wpdb->prefix}pb_bids WHERE performer_id IN ($perf_id_placeholders)",
                        $performer_table_ids
                    )
                );

                // Delete transactions (via booking_id).
                $booking_ids = $wpdb->get_col(
                    $wpdb->prepare(
                        "SELECT id FROM {$wpdb->prefix}pb_bookings WHERE performer_id IN ($perf_id_placeholders)",
                        $performer_table_ids
                    )
                );
                if ( ! empty( $booking_ids ) ) {
                    $booking_placeholders = implode( ',', array_fill( 0, count( $booking_ids ), '%d' ) );
                    $wpdb->query(
                        $wpdb->prepare(
                            "DELETE FROM {$wpdb->prefix}pb_transactions WHERE booking_id IN ($booking_placeholders)",
                            $booking_ids
                        )
                    );
                }

                // Delete microsites.
                $wpdb->query(
                    $wpdb->prepare(
                        "DELETE FROM {$wpdb->prefix}pb_microsites WHERE performer_id IN ($perf_id_placeholders)",
                        $performer_table_ids
                    )
                );
            }

            // Delete performer records.
            $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM {$wpdb->prefix}pb_performers WHERE user_id IN ($perf_placeholders)",
                    $performer_user_ids
                )
            );
        }

        if ( ! empty( $customer_user_ids ) ) {
            $cust_placeholders = implode( ',', array_fill( 0, count( $customer_user_ids ), '%d' ) );

            // Delete customer events.
            $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM {$wpdb->prefix}pb_events WHERE customer_id IN ($cust_placeholders)",
                    $customer_user_ids
                )
            );
        }

        if ( ! empty( $all_user_ids ) ) {
            $all_placeholders = implode( ',', array_fill( 0, count( $all_user_ids ), '%d' ) );

            // Delete reviews.
            $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM {$wpdb->prefix}pb_reviews WHERE reviewer_id IN ($all_placeholders) OR reviewee_id IN ($all_placeholders)",
                    array_merge( $all_user_ids, $all_user_ids )
                )
            );
        }

        // Clear options.
        delete_option( 'peanut_booker_demo_mode' );
        delete_option( 'peanut_booker_demo_data_ids' );

        return true;
    }


    /**
     * Get demo data summary for admin display.
     *
     * @return array Summary data.
     */
    public static function get_demo_summary() {
        global $wpdb;

        if ( ! self::is_demo_mode() ) {
            return array();
        }

        $demo_ids = get_option( 'peanut_booker_demo_data_ids', array() );

        return array(
            'performers'   => count( $demo_ids['performer_user_ids'] ?? array() ),
            'customers'    => count( $demo_ids['customer_user_ids'] ?? array() ),
            'bookings'     => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}pb_bookings" ),
            'reviews'      => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}pb_reviews" ),
            'events'       => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}pb_events" ),
            'bids'         => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}pb_bids" ),
            'transactions' => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}pb_transactions" ),
            'microsites'   => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}pb_microsites" ),
        );
    }
}

// Initialize demo mode hooks.
add_action( 'init', array( 'Peanut_Booker_Demo_Data', 'init' ) );
