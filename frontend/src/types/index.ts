// API Response Types
export interface ApiResponse<T> {
  success: boolean;
  data: T;
  message?: string;
}

export interface PaginationParams {
  page?: number;
  per_page?: number;
  search?: string;
  order_by?: string;
  order?: 'ASC' | 'DESC';
}

export interface PaginatedResponse<T> {
  data: T[];
  total: number;
  total_pages: number;
  page: number;
  per_page: number;
}

// Performer Types
export type PerformerTier = 'free' | 'pro';
export type AchievementLevel = 'bronze' | 'silver' | 'gold' | 'platinum';
export type PerformerStatus = 'active' | 'inactive' | 'pending' | 'suspended';

export interface Performer {
  id: number;
  user_id: number;
  profile_id: number;
  stage_name: string;
  email: string;
  tier: PerformerTier;
  achievement_level: AchievementLevel;
  achievement_score: number;
  completed_bookings: number;
  average_rating: number;
  total_reviews: number;
  profile_completeness: number;
  hourly_rate: number;
  deposit_percentage: number;
  service_radius: number;
  is_verified: boolean;
  is_featured: boolean;
  status: PerformerStatus;
  location_city: string;
  location_state: string;
  tagline?: string;
  experience_years?: number;
  website?: string;
  phone?: string;
  email_public?: string;
  minimum_booking?: number;
  sale_price?: number;
  sale_active?: boolean;
  travel_willing?: boolean;
  travel_radius?: number;
  // Microsite info (if performer has one)
  microsite_id?: number;
  microsite_status?: MicrositeStatus;
  microsite_slug?: string;
  microsite_created_at?: string;
  microsite_views?: number;
  gallery_images?: string[];
  video_links?: string[];
  categories?: Category[];
  service_areas?: ServiceArea[];
  created_at: string;
  updated_at: string;
}

// Booking Types
export type BookingStatus = 'pending' | 'confirmed' | 'completed' | 'cancelled';
export type EscrowStatus = 'pending' | 'held' | 'released' | 'refunded';

export interface Booking {
  id: number;
  booking_number: string;
  performer_id: number;
  performer_name: string;
  customer_id: number;
  customer_name: string;
  customer_email: string;
  event_id?: number;
  bid_id?: number;
  order_id?: number;
  event_title: string;
  event_description?: string;
  event_date: string;
  event_time_start: string;
  event_time_end: string;
  event_location: string;
  event_address?: string;
  event_city: string;
  event_state: string;
  event_zip?: string;
  total_amount: number;
  deposit_amount: number;
  remaining_amount: number;
  commission_amount: number;
  payout_amount: number;
  booking_status: BookingStatus;
  escrow_status: EscrowStatus;
  performer_confirmed: boolean;
  customer_confirmed_completion: boolean;
  completion_date?: string;
  payout_date?: string;
  cancellation_date?: string;
  cancellation_reason?: string;
  notes?: string;
  created_at: string;
  updated_at: string;
}

// Market Event Types
export type MarketEventStatus = 'open' | 'closed' | 'booked' | 'completed' | 'cancelled';

export interface MarketEvent {
  id: number;
  customer_id: number;
  customer_name: string;
  post_id?: number;
  title: string;
  description?: string;
  event_date: string;
  event_time_start: string;
  event_time_end: string;
  event_location: string;
  event_address?: string;
  event_city: string;
  event_state: string;
  event_zip?: string;
  category_id?: number;
  category_name?: string;
  budget_min: number;
  budget_max: number;
  bid_deadline: string;
  auto_deadline_days: number;
  total_bids: number;
  accepted_bid_id?: number;
  status: MarketEventStatus;
  is_featured: boolean;
  created_at: string;
  updated_at: string;
}

export interface Bid {
  id: number;
  event_id: number;
  performer_id: number;
  performer_name: string;
  bid_amount: number;
  message?: string;
  status: 'pending' | 'accepted' | 'declined';
  is_read: boolean;
  created_at: string;
}

// Review Types
export type ArbitrationStatus = 'pending' | 'resolved' | 'dismissed';

export interface Review {
  id: number;
  booking_id: number;
  reviewer_id: number;
  reviewer_name: string;
  reviewer_type: 'customer' | 'performer';
  reviewee_id: number;
  reviewee_name: string;
  rating: number;
  title?: string;
  content: string;
  response?: string;
  response_date?: string;
  is_flagged: boolean;
  flag_reason?: string;
  flagged_by?: number;
  flagged_date?: string;
  arbitration_status?: ArbitrationStatus;
  arbitration_notes?: string;
  arbitrated_by?: number;
  arbitration_date?: string;
  is_visible: boolean;
  created_at: string;
  updated_at: string;
}

// Payout Types
export interface Payout {
  booking_id: number;
  booking_number: string;
  performer_id: number;
  performer_name: string;
  event_date: string;
  completion_date: string;
  total_amount: number;
  commission_amount: number;
  payout_amount: number;
  escrow_status: EscrowStatus;
  auto_release_date: string;
}

// Category & Service Area Types
export interface Category {
  id: number;
  name: string;
  slug: string;
  description?: string;
  count: number;
}

export interface ServiceArea {
  id: number;
  name: string;
  slug: string;
  description?: string;
  count: number;
}

// Dashboard Types
export interface DashboardStats {
  total_performers: number;
  total_bookings: number;
  pending_bookings: number;
  total_revenue: number;
  platform_commission: number;
  reviews_needing_arbitration: number;
  demo_mode: boolean;
}

// Settings Types
export interface Settings {
  // General
  currency: string;
  woocommerce_active: boolean;

  // License
  license_key?: string;
  license_status?: 'active' | 'inactive' | 'expired';
  license_expires?: string;

  // Commission
  free_tier_commission: number;
  pro_tier_commission: number;
  flat_fee_per_transaction: number;

  // Pro Subscription
  pro_monthly_price: number;
  pro_annual_price: number;

  // Booking
  min_deposit_percentage: number;
  max_deposit_percentage: number;
  auto_release_escrow_days: number;

  // Achievements
  silver_threshold: number;
  gold_threshold: number;
  platinum_threshold: number;

  // Google Login
  google_client_id?: string;
  google_client_secret?: string;
}

// Filter Types
export interface PerformerFilters {
  search: string;
  tier: string;
  status: string;
  verified: string;
}

export interface BookingFilters {
  search: string;
  status: string;
  date_from: string;
  date_to: string;
}

export interface MarketFilters {
  search: string;
  status: string;
  date_from: string;
  date_to: string;
}

export interface ReviewFilters {
  search: string;
  flagged: string;
}

// Microsite Types
export type MicrositeStatus = 'active' | 'pending' | 'inactive' | 'expired';
export type MicrositeTemplate = 'classic' | 'modern' | 'bold' | 'minimal';

export interface MicrositeDesignSettings {
  template: MicrositeTemplate;
  primary_color: string;
  secondary_color: string;
  background_color: string;
  text_color: string;
  font_family: string;
  show_reviews: boolean;
  show_calendar: boolean;
  show_booking_button: boolean;
  custom_css?: string;
}

export interface Microsite {
  id: number;
  performer_id: number;
  performer_name: string;
  user_id: number;
  subscription_id?: number;
  status: MicrositeStatus;
  slug: string;
  custom_domain?: string;
  domain_verified: boolean;
  has_custom_domain_addon: boolean;
  design_settings: MicrositeDesignSettings;
  meta_title?: string;
  meta_description?: string;
  view_count: number;
  created_at: string;
  updated_at: string;
}

export interface MicrositeFilters {
  search: string;
  status: string;
}

// Extended Microsite Types for Editor
export type HeroStyle = 'full_width' | 'contained' | 'split';
export type MicrositeSection = 'hero' | 'bio' | 'gallery' | 'reviews' | 'calendar' | 'social' | 'booking';

export interface SocialLinks {
  facebook?: string;
  instagram?: string;
  tiktok?: string;
  youtube?: string;
  twitter?: string;
  linkedin?: string;
}

export interface LayoutSettings {
  hero_style: HeroStyle;
  sections_order: MicrositeSection[];
  show_external_gigs: boolean;
  external_gig_privacy: 'public' | 'private_only';
}

export interface ExtendedMicrositeDesignSettings extends MicrositeDesignSettings {
  social_links: SocialLinks;
  layout_settings: LayoutSettings;
}

export interface ExternalGig {
  id: number;
  performer_id: number;
  date: string;
  event_name: string;
  venue_name?: string;
  event_location?: string;
  event_time?: string;
  is_public: boolean;
  ticket_url?: string;
}

export interface MicrositePreviewPerformer {
  id: number;
  stage_name: string;
  tagline?: string;
  bio?: string;
  hourly_rate: number;
  minimum_booking?: number;
  deposit_percentage?: number;
  sale_price?: number;
  sale_active?: boolean;
  location_city?: string;
  location_state?: string;
  travel_willing?: boolean;
  travel_radius?: number;
  profile_photo: string;
  gallery: { id: number; url: string }[];
  categories: string[];
  service_areas: string[];
}

export interface MicrositePreviewReview {
  id: number;
  rating: number;
  content: string;
  reviewer: string;
  created_at: string;
}

export interface MicrositePreviewData {
  microsite: Microsite;
  performer: MicrositePreviewPerformer | null;
  reviews: MicrositePreviewReview[];
  external_gigs: ExternalGig[];
  preview_url: string;
}

export interface MicrositeAnalytics {
  total_views: number;
  unique_visitors: number;
  booking_clicks: number;
  views_by_day: { date: string; views: number }[];
  top_referrers: { domain: string; count: number }[];
  popular_hours: { hour: number; count: number }[];
  date_range: {
    start: string;
    end: string;
  };
}
