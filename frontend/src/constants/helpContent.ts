/**
 * Centralized help content for the Peanut Booker admin interface.
 * All tooltips, help cards, and guidance text in one place for easy maintenance.
 */

// ============================================================================
// DASHBOARD
// ============================================================================
export const dashboard = {
  helpCard: {
    title: 'Welcome to Your Dashboard',
    content: 'This is your command center. Monitor bookings, track revenue, and keep an eye on platform activity all in one place.',
  },
  metrics: {
    totalRevenue: 'Total revenue from completed bookings after platform commission',
    pendingPayouts: 'Funds being held in escrow awaiting release to performers',
    activePerformers: 'Performers currently accepting bookings on the platform',
    conversionRate: 'Percentage of marketplace bids that resulted in bookings',
  },
};

// ============================================================================
// MARKET EVENTS (Bidding Marketplace)
// ============================================================================
export const marketEvents = {
  helpCard: {
    title: 'How the Bidding Marketplace Works',
    content: `Customers post events they need performers for, setting their budget and preferred date.
Performers browse these opportunities and submit bids with their proposed rates.
When a customer accepts a bid, it automatically creates a confirmed booking.`,
    bullets: [
      'Customers set budgets and deadlines for receiving bids',
      'Performers compete by offering their best rates',
      'Accepted bids become confirmed bookings instantly',
    ],
  },
  columns: {
    customer: 'The person looking to hire a performer',
    eventType: 'The category of entertainment needed',
    budgetRange: 'The price range the customer is willing to pay',
    bidDeadline: 'Last day performers can submit bids',
    bidsReceived: 'Number of performers who have submitted proposals',
  },
  status: {
    open: 'Accepting bids from performers',
    pending: 'Waiting for customer to review bids',
    accepted: 'Customer has chosen a performer',
    expired: 'Bid deadline passed without acceptance',
    cancelled: 'Customer cancelled the request',
  },
  filters: {
    status: 'Filter events by their current stage',
    eventType: 'Show only specific types of events',
  },
};

// ============================================================================
// BOOKINGS
// ============================================================================
export const bookings = {
  helpCard: {
    title: 'Understanding the Booking Lifecycle',
    content: `Every booking flows through these stages: Pending (awaiting confirmation),
Confirmed (performer accepted), Completed (event finished), and Payout Released (funds sent to performer).
The escrow system protects both parties throughout this process.`,
    bullets: [
      'Funds are held safely until the event completes',
      'Commission is calculated automatically',
      'Payouts can be released manually if needed',
    ],
  },
  columns: {
    booking: 'Unique booking reference and event details',
    customer: 'The person who made the booking',
    performer: 'The entertainer providing services',
    amount: 'Total booking price before commission',
    commission: 'Platform fee deducted from the total',
    escrow: 'Current status of the held funds',
    status: 'Where this booking is in its lifecycle',
  },
  status: {
    pending: 'Waiting for performer to confirm availability',
    confirmed: 'Performer accepted, awaiting event date',
    completed: 'Event finished successfully',
    cancelled: 'Booking was cancelled',
    refunded: 'Customer received their money back',
  },
  escrow: {
    pending: 'Payment not yet received from customer',
    full_held: 'Full amount held securely in escrow',
    partial_held: 'Deposit held, remaining due at event',
    released: 'Funds sent to performer',
    refunded: 'Funds returned to customer',
  },
};

// ============================================================================
// PERFORMERS
// ============================================================================
export const performers = {
  helpCard: {
    title: 'Managing Your Performer Network',
    content: `Performers are the heart of your marketplace. Each performer has a profile,
tier level, and verification status that affects their visibility and credibility.`,
    bullets: [
      'Higher tiers get priority in search results',
      'Verified performers display a trust badge',
      'Achievement levels unlock based on completed bookings',
    ],
  },
  columns: {
    name: 'Performer display name and primary skill',
    tier: 'Subscription level affecting features and visibility',
    level: 'Achievement rank based on completed bookings',
    verified: 'Identity confirmed through verification process',
    rating: 'Average score from customer reviews',
    bookings: 'Total number of completed bookings',
    status: 'Whether the performer can accept new bookings',
  },
  tier: {
    free: 'Basic listing with limited visibility',
    starter: 'Enhanced profile with more photo slots',
    pro: 'Priority placement and advanced analytics',
    elite: 'Maximum visibility and premium support',
  },
  level: {
    newcomer: 'Just getting started (0-4 bookings)',
    rising: 'Building momentum (5-14 bookings)',
    established: 'Proven track record (15-49 bookings)',
    expert: 'Top performer (50+ bookings)',
  },
  status: {
    active: 'Accepting new bookings',
    inactive: 'Profile hidden from search',
    suspended: 'Account temporarily disabled',
    pending: 'Awaiting profile approval',
  },
};

// ============================================================================
// PERFORMER EDITOR
// ============================================================================
export const performerEditor = {
  tabs: {
    profile: 'Basic information and bio',
    services: 'What the performer offers',
    pricing: 'Rates and packages',
    media: 'Photos and videos',
    availability: 'Schedule and booking rules',
    settings: 'Account preferences',
  },
  fields: {
    displayName: 'The name shown to customers (can be a stage name)',
    bio: 'A compelling description to attract bookings',
    category: 'Primary type of performance',
    hourlyRate: 'Standard rate charged per hour',
    salePrice: 'Discounted rate (leave empty if no discount)',
    depositPercent: 'Percentage required upfront to secure booking',
    minBookingHours: 'Shortest booking duration you accept',
    travelRadius: 'Maximum distance willing to travel (in miles)',
    setupTime: 'Minutes needed before performance begins',
    breakdownTime: 'Minutes needed after performance ends',
  },
  pricing: {
    hourlyVsPackage: 'Hourly rates are flexible; packages offer fixed-price options',
    deposit: 'Higher deposits reduce no-shows but may deter some customers',
    salePrice: 'Promotional pricing shown with strikethrough original',
  },
};

// ============================================================================
// REVIEWS
// ============================================================================
export const reviews = {
  helpCard: {
    title: 'Review Moderation Guidelines',
    content: `Reviews build trust in your marketplace. Only remove reviews that violate guidelines -
personal opinions, even negative ones, should generally remain visible.`,
    bullets: [
      'Remove reviews with offensive language or personal attacks',
      'Keep genuine negative feedback - it builds authenticity',
      'Flagged reviews need your attention',
      'Consider both sides before taking action',
    ],
  },
  columns: {
    customer: 'Person who wrote the review',
    performer: 'Performer being reviewed',
    rating: 'Star rating given (1-5)',
    content: 'The written review text',
    flagged: 'Marked for moderation review',
    visible: 'Whether customers can see this review',
  },
  actions: {
    hide: 'Remove from public view without deleting',
    show: 'Make visible to customers',
    delete: 'Permanently remove (cannot be undone)',
  },
  flagReasons: {
    inappropriate: 'Contains offensive content',
    spam: 'Promotional or fake review',
    irrelevant: 'Not about the actual service',
    harassment: 'Personal attacks or threats',
  },
};

// ============================================================================
// MICROSITES
// ============================================================================
export const microsites = {
  helpCard: {
    title: 'What Are Microsites?',
    content: `Microsites are personalized landing pages for performers. Each microsite has its own
URL and showcases a performer's profile, portfolio, and booking information.`,
    bullets: [
      'Performers can share their microsite link anywhere',
      'Custom domains require the premium add-on',
      'Templates control the visual style',
    ],
  },
  columns: {
    performer: 'The performer this microsite belongs to',
    slug: 'The URL path (e.g., /performer/john-smith)',
    status: 'Whether the microsite is publicly accessible',
    views: 'Number of times the page has been visited',
    template: 'Visual theme applied to the page',
  },
  fields: {
    slug: 'URL-friendly name (letters, numbers, hyphens only)',
    template: 'Visual design theme for the page',
    primaryColor: 'Brand color used for buttons and accents',
    customDomain: 'Connect your own domain (premium feature)',
    metaTitle: 'Page title shown in browser tabs and search results',
    metaDescription: 'Summary shown in search engine results',
  },
  templates: {
    classic: 'Traditional layout with sidebar navigation',
    modern: 'Clean, minimalist design with large images',
    bold: 'High-impact design with vibrant colors',
    minimal: 'Simple, text-focused layout',
  },
  status: {
    active: 'Publicly visible and accessible',
    pending: 'Awaiting initial setup completion',
    suspended: 'Temporarily hidden from public',
  },
};

// ============================================================================
// PAYOUTS
// ============================================================================
export const payouts = {
  helpCard: {
    title: 'How Payouts Work',
    content: `When a booking completes, funds move to escrow. After the release period
(or manual approval), the performer receives their payment minus commission.`,
    bullets: [
      'Auto-release happens after the configured waiting period',
      'Manual release is useful for dispute resolution',
      'Commission is deducted automatically',
    ],
  },
  columns: {
    booking: 'The completed booking this payout relates to',
    performer: 'Who will receive the payment',
    amount: 'Total booking amount before deductions',
    commission: 'Platform fee being deducted',
    payout: 'Final amount the performer receives',
    status: 'Current state of the payout',
  },
  status: {
    pending: 'Awaiting release (still in escrow)',
    processing: 'Payment is being sent',
    completed: 'Funds successfully transferred',
    failed: 'Transfer unsuccessful - needs attention',
  },
  actions: {
    release: 'Send funds to performer immediately',
    hold: 'Delay release for investigation',
  },
};

// ============================================================================
// CUSTOMERS
// ============================================================================
export const customers = {
  helpCard: {
    title: 'Your Customer Base',
    content: `These are the people booking performers through your platform. Track their activity,
spending habits, and lifetime value to understand your audience.`,
  },
  columns: {
    customer: 'Customer name and contact information',
    bookings: 'Total number of bookings made',
    spent: 'Total amount spent on the platform',
    lastBooking: 'Most recent booking date',
    joined: 'When they created their account',
  },
};

// ============================================================================
// MESSAGES
// ============================================================================
export const messages = {
  helpCard: {
    title: 'Message Monitoring',
    content: `As an admin, you can view conversations between customers and performers.
This helps with dispute resolution and ensuring professional communication.`,
    note: 'You cannot send messages as an admin - this is read-only access.',
  },
};

// ============================================================================
// SETTINGS
// ============================================================================
export const settings = {
  general: {
    platformName: 'Your marketplace brand name shown throughout the site',
    supportEmail: 'Where customer support inquiries are sent',
    currency: 'Default currency for all transactions',
    timezone: 'Used for scheduling and date displays',
  },
  commission: {
    helpCard: {
      title: 'Setting Commission Rates',
      content: `Commission is the percentage you keep from each booking.
Consider your operating costs and competitor rates when setting this.`,
    },
    defaultRate: 'Percentage deducted from each booking (e.g., 15% = 0.15)',
    tierOverrides: 'Different rates for different performer tiers',
  },
  escrow: {
    helpCard: {
      title: 'Escrow Protection',
      content: `Escrow holds customer payments until the event completes successfully.
This protects both parties from fraud and no-shows.`,
    },
    releaseDelay: 'Days to wait after event completion before auto-releasing funds',
    autoRelease: 'Whether to release funds automatically or require manual approval',
  },
  googleOAuth: {
    helpCard: {
      title: 'Google Calendar Integration',
      content: `Connect Google OAuth to allow performers to sync their availability
with Google Calendar. This reduces double-bookings and scheduling conflicts.`,
      bullets: [
        'Create credentials in Google Cloud Console',
        'Enable the Google Calendar API',
        'Add authorized redirect URIs',
      ],
    },
    clientId: 'From Google Cloud Console > APIs & Services > Credentials',
    clientSecret: 'Keep this private - never share publicly',
  },
};

// ============================================================================
// DEMO MODE
// ============================================================================
export const demoMode = {
  helpCard: {
    title: 'About Demo Mode',
    content: `Demo mode populates your platform with sample data so you can explore
all features without affecting real customers or performers.`,
    bullets: [
      'Creates sample performers, bookings, and reviews',
      'All demo data is clearly marked',
      'Disabling removes all demo data completely',
      'Safe to enable/disable at any time',
    ],
  },
  warning: 'Demo data will be mixed with any real data you have. Consider using on a fresh installation.',
};

// ============================================================================
// ANALYTICS
// ============================================================================
export const analytics = {
  metrics: {
    revenue: 'Total earnings from completed bookings',
    bookings: 'Number of bookings in the selected period',
    avgBookingValue: 'Average amount per booking',
    repeatCustomerRate: 'Percentage of customers who book again',
  },
  charts: {
    revenueOverTime: 'Track your earnings growth',
    bookingsByCategory: 'See which performer types are most popular',
    topPerformers: 'Your highest-earning performers',
  },
};

// ============================================================================
// COMMON / SHARED
// ============================================================================
export const common = {
  actions: {
    edit: 'Make changes to this item',
    delete: 'Permanently remove this item',
    view: 'See full details',
    export: 'Download as spreadsheet',
  },
  pagination: {
    perPage: 'Number of items shown per page',
  },
  filters: {
    search: 'Search by name, email, or ID',
    dateRange: 'Filter by date range',
    status: 'Filter by current status',
  },
  empty: {
    noResults: 'No items match your current filters',
    noData: 'Nothing here yet',
  },
};
