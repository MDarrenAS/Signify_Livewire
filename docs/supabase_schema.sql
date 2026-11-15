-- WARNING: This schema is for context only and is not meant to be run.
-- Table order and constraints may not be valid for execution.

CREATE TABLE public.migrations (
  id integer NOT NULL DEFAULT nextval('migrations_id_seq'::regclass),
  migration character varying NOT NULL,
  batch integer NOT NULL,
  CONSTRAINT migrations_pkey PRIMARY KEY (id)
);
CREATE TABLE public.users (
  id bigint NOT NULL DEFAULT nextval('users_id_seq'::regclass),
  name character varying NOT NULL,
  email character varying NOT NULL UNIQUE,
  email_verified_at timestamp without time zone,
  password character varying NOT NULL,
  remember_token character varying,
  created_at timestamp without time zone,
  updated_at timestamp without time zone,
  two_factor_secret text,
  two_factor_recovery_codes text,
  two_factor_confirmed_at timestamp without time zone,
  role character varying NOT NULL DEFAULT 'user'::character varying,
  is_active boolean NOT NULL DEFAULT true,
  CONSTRAINT users_pkey PRIMARY KEY (id)
);
CREATE TABLE public.password_reset_tokens (
  email character varying NOT NULL,
  token character varying NOT NULL,
  created_at timestamp without time zone,
  CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email)
);
CREATE TABLE public.sessions (
  id character varying NOT NULL,
  user_id bigint,
  ip_address character varying,
  user_agent text,
  payload text NOT NULL,
  last_activity integer NOT NULL,
  CONSTRAINT sessions_pkey PRIMARY KEY (id)
);
CREATE TABLE public.cache (
  key character varying NOT NULL,
  value text NOT NULL,
  expiration bigint NOT NULL,
  CONSTRAINT cache_pkey PRIMARY KEY (key)
);
CREATE TABLE public.cache_locks (
  key character varying NOT NULL,
  owner character varying NOT NULL,
  expiration bigint NOT NULL,
  CONSTRAINT cache_locks_pkey PRIMARY KEY (key)
);
CREATE TABLE public.jobs (
  id bigint NOT NULL DEFAULT nextval('jobs_id_seq'::regclass),
  queue character varying NOT NULL,
  payload text NOT NULL,
  attempts smallint NOT NULL,
  reserved_at integer,
  available_at integer NOT NULL,
  created_at integer NOT NULL,
  CONSTRAINT jobs_pkey PRIMARY KEY (id)
);
CREATE TABLE public.job_batches (
  id character varying NOT NULL,
  name character varying NOT NULL,
  total_jobs integer NOT NULL,
  pending_jobs integer NOT NULL,
  failed_jobs integer NOT NULL,
  failed_job_ids text NOT NULL,
  options text,
  cancelled_at integer,
  created_at integer NOT NULL,
  finished_at integer,
  CONSTRAINT job_batches_pkey PRIMARY KEY (id)
);
CREATE TABLE public.failed_jobs (
  id bigint NOT NULL DEFAULT nextval('failed_jobs_id_seq'::regclass),
  uuid character varying NOT NULL UNIQUE,
  connection character varying NOT NULL,
  queue character varying NOT NULL,
  payload text NOT NULL,
  exception text NOT NULL,
  failed_at timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT failed_jobs_pkey PRIMARY KEY (id)
);
CREATE TABLE public.passkeys (
  id bigint NOT NULL DEFAULT nextval('passkeys_id_seq'::regclass),
  user_id bigint NOT NULL,
  name character varying NOT NULL,
  credential_id character varying NOT NULL UNIQUE,
  credential json NOT NULL,
  last_used_at timestamp without time zone,
  created_at timestamp without time zone,
  updated_at timestamp without time zone,
  CONSTRAINT passkeys_pkey PRIMARY KEY (id),
  CONSTRAINT passkeys_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id)
);
CREATE TABLE public.library_categories (
  id bigint GENERATED ALWAYS AS IDENTITY NOT NULL,
  name character varying NOT NULL,
  slug character varying NOT NULL UNIQUE CHECK (slug::text ~ '^[a-z0-9]+(-[a-z0-9]+)*$'::text),
  description text,
  sort_order integer NOT NULL DEFAULT 0,
  status text NOT NULL DEFAULT 'published'::text CHECK (status = ANY (ARRAY['draft'::text, 'published'::text, 'archived'::text])),
  created_at timestamp with time zone NOT NULL DEFAULT now(),
  updated_at timestamp with time zone NOT NULL DEFAULT now(),
  created_by bigint,
  CONSTRAINT library_categories_pkey PRIMARY KEY (id),
  CONSTRAINT library_categories_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.users(id)
);
CREATE TABLE public.library_items (
  id bigint GENERATED ALWAYS AS IDENTITY NOT NULL,
  category_id bigint NOT NULL,
  title character varying NOT NULL,
  slug character varying NOT NULL UNIQUE CHECK (slug::text ~ '^[a-z0-9]+(-[a-z0-9]+)*$'::text),
  item_type text NOT NULL DEFAULT 'word'::text CHECK (item_type = ANY (ARRAY['alphabet'::text, 'number'::text, 'word'::text, 'phrase'::text, 'expression'::text])),
  meaning text,
  description text,
  sort_order integer NOT NULL DEFAULT 0,
  status text NOT NULL DEFAULT 'draft'::text CHECK (status = ANY (ARRAY['draft'::text, 'published'::text, 'archived'::text])),
  created_at timestamp with time zone NOT NULL DEFAULT now(),
  updated_at timestamp with time zone NOT NULL DEFAULT now(),
  movement_description text,
  example_usage text,
  created_by bigint,
  CONSTRAINT library_items_pkey PRIMARY KEY (id),
  CONSTRAINT library_items_category_id_fkey FOREIGN KEY (category_id) REFERENCES public.library_categories(id),
  CONSTRAINT library_items_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.users(id)
);
CREATE TABLE public.library_media (
  id bigint GENERATED ALWAYS AS IDENTITY NOT NULL,
  library_item_id bigint NOT NULL,
  media_type text NOT NULL CHECK (media_type = ANY (ARRAY['image'::text, 'video'::text])),
  storage_path text NOT NULL UNIQUE,
  mime_type character varying,
  file_size_bytes bigint CHECK (file_size_bytes IS NULL OR file_size_bytes >= 0),
  alt_text character varying,
  sort_order integer NOT NULL DEFAULT 0,
  is_primary boolean NOT NULL DEFAULT false,
  created_at timestamp with time zone NOT NULL DEFAULT now(),
  updated_at timestamp with time zone NOT NULL DEFAULT now(),
  thumbnail_path text,
  caption character varying,
  duration_seconds integer CHECK (duration_seconds IS NULL OR duration_seconds >= 0),
  uploaded_by bigint,
  CONSTRAINT library_media_pkey PRIMARY KEY (id),
  CONSTRAINT library_media_library_item_id_fkey FOREIGN KEY (library_item_id) REFERENCES public.library_items(id),
  CONSTRAINT library_media_uploaded_by_fkey FOREIGN KEY (uploaded_by) REFERENCES public.users(id)
);
CREATE TABLE public.modules (
  id bigint GENERATED ALWAYS AS IDENTITY NOT NULL,
  title character varying NOT NULL,
  slug character varying NOT NULL UNIQUE CHECK (slug::text ~ '^[a-z0-9]+(-[a-z0-9]+)*$'::text),
  description text,
  thumbnail_path text,
  level text NOT NULL DEFAULT 'beginner'::text CHECK (level = ANY (ARRAY['beginner'::text, 'intermediate'::text, 'advanced'::text])),
  sort_order integer NOT NULL DEFAULT 0,
  status text NOT NULL DEFAULT 'draft'::text CHECK (status = ANY (ARRAY['draft'::text, 'published'::text, 'archived'::text])),
  created_at timestamp with time zone NOT NULL DEFAULT now(),
  updated_at timestamp with time zone NOT NULL DEFAULT now(),
  created_by bigint,
  CONSTRAINT modules_pkey PRIMARY KEY (id),
  CONSTRAINT modules_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.users(id)
);
CREATE TABLE public.module_lessons (
  id bigint GENERATED ALWAYS AS IDENTITY NOT NULL,
  module_id bigint NOT NULL,
  title character varying NOT NULL,
  slug character varying NOT NULL CHECK (slug::text ~ '^[a-z0-9]+(-[a-z0-9]+)*$'::text),
  description text,
  content text,
  sort_order integer NOT NULL DEFAULT 0,
  status text NOT NULL DEFAULT 'draft'::text CHECK (status = ANY (ARRAY['draft'::text, 'published'::text, 'archived'::text])),
  created_at timestamp with time zone NOT NULL DEFAULT now(),
  updated_at timestamp with time zone NOT NULL DEFAULT now(),
  lesson_type text NOT NULL DEFAULT 'lesson'::text,
  summary text,
  estimated_minutes integer CHECK (estimated_minutes IS NULL OR estimated_minutes >= 0),
  created_by bigint,
  CONSTRAINT module_lessons_pkey PRIMARY KEY (id),
  CONSTRAINT module_lessons_module_id_fkey FOREIGN KEY (module_id) REFERENCES public.modules(id),
  CONSTRAINT module_lessons_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.users(id)
);
CREATE TABLE public.lesson_library_items (
  id bigint GENERATED ALWAYS AS IDENTITY NOT NULL,
  lesson_id bigint NOT NULL,
  library_item_id bigint NOT NULL,
  sort_order integer NOT NULL DEFAULT 0,
  created_at timestamp with time zone NOT NULL DEFAULT now(),
  updated_at timestamp with time zone NOT NULL DEFAULT now(),
  CONSTRAINT lesson_library_items_pkey PRIMARY KEY (id),
  CONSTRAINT lesson_library_items_lesson_id_fkey FOREIGN KEY (lesson_id) REFERENCES public.module_lessons(id),
  CONSTRAINT lesson_library_items_library_item_id_fkey FOREIGN KEY (library_item_id) REFERENCES public.library_items(id)
);
CREATE TABLE public.lesson_media (
  id bigint GENERATED ALWAYS AS IDENTITY NOT NULL,
  lesson_id bigint NOT NULL,
  media_type text NOT NULL CHECK (media_type = ANY (ARRAY['image'::text, 'video'::text])),
  storage_path text NOT NULL UNIQUE,
  mime_type character varying,
  file_size_bytes bigint CHECK (file_size_bytes IS NULL OR file_size_bytes >= 0),
  alt_text character varying,
  sort_order integer NOT NULL DEFAULT 0,
  created_at timestamp with time zone NOT NULL DEFAULT now(),
  updated_at timestamp with time zone NOT NULL DEFAULT now(),
  caption character varying,
  CONSTRAINT lesson_media_pkey PRIMARY KEY (id),
  CONSTRAINT lesson_media_lesson_id_fkey FOREIGN KEY (lesson_id) REFERENCES public.module_lessons(id)
);
CREATE TABLE public.quizzes (
  id bigint GENERATED ALWAYS AS IDENTITY NOT NULL,
  module_id bigint,
  title character varying NOT NULL,
  slug character varying NOT NULL UNIQUE CHECK (slug::text ~ '^[a-z0-9]+(-[a-z0-9]+)*$'::text),
  description text,
  passing_score numeric NOT NULL DEFAULT 70 CHECK (passing_score >= 0::numeric AND passing_score <= 100::numeric),
  status text NOT NULL DEFAULT 'draft'::text CHECK (status = ANY (ARRAY['draft'::text, 'published'::text, 'archived'::text])),
  created_at timestamp with time zone NOT NULL DEFAULT now(),
  updated_at timestamp with time zone NOT NULL DEFAULT now(),
  lesson_id bigint,
  time_limit_minutes integer CHECK (time_limit_minutes IS NULL OR time_limit_minutes > 0),
  attempt_limit integer CHECK (attempt_limit IS NULL OR attempt_limit > 0),
  created_by bigint,
  CONSTRAINT quizzes_pkey PRIMARY KEY (id),
  CONSTRAINT quizzes_module_id_fkey FOREIGN KEY (module_id) REFERENCES public.modules(id),
  CONSTRAINT quizzes_lesson_id_fkey FOREIGN KEY (lesson_id) REFERENCES public.module_lessons(id),
  CONSTRAINT quizzes_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.users(id)
);
CREATE TABLE public.quiz_questions (
  id bigint GENERATED ALWAYS AS IDENTITY NOT NULL,
  quiz_id bigint NOT NULL,
  library_item_id bigint,
  question_text text,
  question_type text NOT NULL DEFAULT 'multiple_choice'::text CHECK (question_type = ANY (ARRAY['multiple_choice'::text, 'true_false'::text, 'sign_to_text'::text, 'text_to_sign'::text, 'matching'::text])),
  points numeric NOT NULL DEFAULT 1 CHECK (points >= 0::numeric),
  sort_order integer NOT NULL DEFAULT 0,
  created_at timestamp with time zone NOT NULL DEFAULT now(),
  updated_at timestamp with time zone NOT NULL DEFAULT now(),
  explanation text,
  library_media_id bigint,
  CONSTRAINT quiz_questions_pkey PRIMARY KEY (id),
  CONSTRAINT quiz_questions_quiz_id_fkey FOREIGN KEY (quiz_id) REFERENCES public.quizzes(id),
  CONSTRAINT quiz_questions_library_item_id_fkey FOREIGN KEY (library_item_id) REFERENCES public.library_items(id),
  CONSTRAINT quiz_questions_library_media_id_fkey FOREIGN KEY (library_media_id) REFERENCES public.library_media(id)
);
CREATE TABLE public.quiz_options (
  id bigint GENERATED ALWAYS AS IDENTITY NOT NULL,
  question_id bigint NOT NULL,
  library_item_id bigint,
  option_text character varying,
  is_correct boolean NOT NULL DEFAULT false,
  sort_order integer NOT NULL DEFAULT 0,
  created_at timestamp with time zone NOT NULL DEFAULT now(),
  updated_at timestamp with time zone NOT NULL DEFAULT now(),
  library_media_id bigint,
  CONSTRAINT quiz_options_pkey PRIMARY KEY (id),
  CONSTRAINT quiz_options_library_item_id_fkey FOREIGN KEY (library_item_id) REFERENCES public.library_items(id),
  CONSTRAINT quiz_options_question_id_fkey FOREIGN KEY (question_id) REFERENCES public.quiz_questions(id),
  CONSTRAINT quiz_options_library_media_id_fkey FOREIGN KEY (library_media_id) REFERENCES public.library_media(id)
);
CREATE TABLE public.user_lesson_progress (
  id bigint GENERATED ALWAYS AS IDENTITY NOT NULL,
  user_id bigint NOT NULL,
  lesson_id bigint NOT NULL,
  completed_at timestamp with time zone,
  created_at timestamp with time zone NOT NULL DEFAULT now(),
  updated_at timestamp with time zone NOT NULL DEFAULT now(),
  status text NOT NULL DEFAULT 'in_progress'::text CHECK (status = ANY (ARRAY['in_progress'::text, 'completed'::text])),
  progress_percent numeric NOT NULL DEFAULT 0 CHECK (progress_percent >= 0::numeric AND progress_percent <= 100::numeric),
  started_at timestamp with time zone NOT NULL DEFAULT now(),
  last_accessed_at timestamp with time zone NOT NULL DEFAULT now(),
  CONSTRAINT user_lesson_progress_pkey PRIMARY KEY (id),
  CONSTRAINT user_lesson_progress_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id),
  CONSTRAINT user_lesson_progress_lesson_id_fkey FOREIGN KEY (lesson_id) REFERENCES public.module_lessons(id)
);
CREATE TABLE public.quiz_attempts (
  id bigint GENERATED ALWAYS AS IDENTITY NOT NULL,
  user_id bigint NOT NULL,
  quiz_id bigint NOT NULL,
  status text NOT NULL DEFAULT 'in_progress'::text CHECK (status = ANY (ARRAY['in_progress'::text, 'submitted'::text])),
  score numeric CHECK (score IS NULL OR score >= 0::numeric AND score <= 100::numeric),
  points_earned numeric CHECK (points_earned IS NULL OR points_earned >= 0::numeric),
  points_total numeric CHECK (points_total IS NULL OR points_total >= 0::numeric),
  passed boolean,
  started_at timestamp with time zone NOT NULL DEFAULT now(),
  submitted_at timestamp with time zone,
  created_at timestamp with time zone NOT NULL DEFAULT now(),
  updated_at timestamp with time zone NOT NULL DEFAULT now(),
  attempt_number integer NOT NULL DEFAULT 1,
  CONSTRAINT quiz_attempts_pkey PRIMARY KEY (id),
  CONSTRAINT quiz_attempts_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id),
  CONSTRAINT quiz_attempts_quiz_id_fkey FOREIGN KEY (quiz_id) REFERENCES public.quizzes(id)
);
CREATE TABLE public.quiz_attempt_answers (
  id bigint GENERATED ALWAYS AS IDENTITY NOT NULL,
  attempt_id bigint NOT NULL,
  question_id bigint NOT NULL,
  selected_option_id bigint,
  is_correct boolean NOT NULL DEFAULT false,
  created_at timestamp with time zone NOT NULL DEFAULT now(),
  updated_at timestamp with time zone NOT NULL DEFAULT now(),
  points_awarded numeric NOT NULL DEFAULT 0,
  answered_at timestamp with time zone NOT NULL DEFAULT now(),
  CONSTRAINT quiz_attempt_answers_pkey PRIMARY KEY (id),
  CONSTRAINT quiz_attempt_answers_attempt_id_fkey FOREIGN KEY (attempt_id) REFERENCES public.quiz_attempts(id),
  CONSTRAINT quiz_attempt_answers_question_id_fkey FOREIGN KEY (question_id) REFERENCES public.quiz_questions(id),
  CONSTRAINT quiz_attempt_answers_selected_option_id_fkey FOREIGN KEY (selected_option_id) REFERENCES public.quiz_options(id)
);
CREATE TABLE public.quiz_matching_pairs (
  id bigint GENERATED ALWAYS AS IDENTITY NOT NULL,
  question_id bigint NOT NULL,
  library_media_id bigint NOT NULL,
  correct_library_item_id bigint NOT NULL,
  sort_order integer NOT NULL DEFAULT 0,
  points numeric NOT NULL DEFAULT 1 CHECK (points >= 0::numeric),
  created_at timestamp with time zone NOT NULL DEFAULT now(),
  updated_at timestamp with time zone NOT NULL DEFAULT now(),
  CONSTRAINT quiz_matching_pairs_pkey PRIMARY KEY (id),
  CONSTRAINT quiz_matching_pairs_question_id_fkey FOREIGN KEY (question_id) REFERENCES public.quiz_questions(id),
  CONSTRAINT quiz_matching_pairs_library_media_id_fkey FOREIGN KEY (library_media_id) REFERENCES public.library_media(id),
  CONSTRAINT quiz_matching_pairs_correct_library_item_id_fkey FOREIGN KEY (correct_library_item_id) REFERENCES public.library_items(id)
);
CREATE TABLE public.quiz_matching_responses (
  id bigint GENERATED ALWAYS AS IDENTITY NOT NULL,
  attempt_id bigint NOT NULL,
  matching_pair_id bigint NOT NULL,
  selected_library_item_id bigint,
  is_correct boolean NOT NULL DEFAULT false,
  points_awarded numeric NOT NULL DEFAULT 0,
  answered_at timestamp with time zone NOT NULL DEFAULT now(),
  created_at timestamp with time zone NOT NULL DEFAULT now(),
  updated_at timestamp with time zone NOT NULL DEFAULT now(),
  CONSTRAINT quiz_matching_responses_pkey PRIMARY KEY (id),
  CONSTRAINT quiz_matching_responses_attempt_id_fkey FOREIGN KEY (attempt_id) REFERENCES public.quiz_attempts(id),
  CONSTRAINT quiz_matching_responses_matching_pair_id_fkey FOREIGN KEY (matching_pair_id) REFERENCES public.quiz_matching_pairs(id),
  CONSTRAINT quiz_matching_responses_selected_library_item_id_fkey FOREIGN KEY (selected_library_item_id) REFERENCES public.library_items(id)
);
