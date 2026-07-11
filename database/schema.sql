CREATE TABLE IF NOT EXISTS users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  spotify_user_id VARCHAR(191) NOT NULL UNIQUE,
  display_name VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS artists (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  spotify_id VARCHAR(191) NOT NULL UNIQUE,
  name VARCHAR(255) NOT NULL,
  image_url TEXT NULL,
  genres JSON NULL,
  popularity TINYINT UNSIGNED NOT NULL DEFAULT 0,
  global_elo DECIMAL(10,2) NOT NULL DEFAULT 1000.00,
  wins INT UNSIGNED NOT NULL DEFAULT 0,
  losses INT UNSIGNED NOT NULL DEFAULT 0,
  matches INT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_artists_elo (global_elo),
  INDEX idx_artists_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_artists (
  user_id BIGINT UNSIGNED NOT NULL,
  artist_id BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (user_id, artist_id),
  CONSTRAINT fk_user_artists_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_user_artists_artist FOREIGN KEY (artist_id) REFERENCES artists(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS votes (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  artist_a_id BIGINT UNSIGNED NOT NULL,
  artist_b_id BIGINT UNSIGNED NOT NULL,
  winner_artist_id BIGINT UNSIGNED NOT NULL,
  loser_artist_id BIGINT UNSIGNED NOT NULL,
  elo_before_winner DECIMAL(10,2) NOT NULL,
  elo_after_winner DECIMAL(10,2) NOT NULL,
  elo_before_loser DECIMAL(10,2) NOT NULL,
  elo_after_loser DECIMAL(10,2) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_votes_user (user_id),
  INDEX idx_votes_winner (winner_artist_id),
  INDEX idx_votes_created (created_at),
  CONSTRAINT fk_votes_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_votes_artist_a FOREIGN KEY (artist_a_id) REFERENCES artists(id) ON DELETE RESTRICT,
  CONSTRAINT fk_votes_artist_b FOREIGN KEY (artist_b_id) REFERENCES artists(id) ON DELETE RESTRICT,
  CONSTRAINT fk_votes_winner FOREIGN KEY (winner_artist_id) REFERENCES artists(id) ON DELETE RESTRICT,
  CONSTRAINT fk_votes_loser FOREIGN KEY (loser_artist_id) REFERENCES artists(id) ON DELETE RESTRICT,
  CONSTRAINT chk_votes_distinct_pair CHECK (artist_a_id <> artist_b_id),
  CONSTRAINT chk_votes_distinct_result CHECK (winner_artist_id <> loser_artist_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
