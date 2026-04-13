INSERT INTO equipment_sport (equipment_id, sport_id, created_at, updated_at) VALUES
(1, 1, DATETIME('now'), DATETIME('now')), -- Vélo de montagne → Vélo
(1, 5, DATETIME('now'), DATETIME('now')), -- Vélo de montagne → Randonnée
(2, 1, DATETIME('now'), DATETIME('now')), -- Casque → Vélo
(2, 2, DATETIME('now'), DATETIME('now')), -- Casque → Ski
(3, 2, DATETIME('now'), DATETIME('now')), -- Skis → Ski
(4, 4, DATETIME('now'), DATETIME('now')), -- Raquette → Tennis
(5, 5, DATETIME('now'), DATETIME('now')); -- Sac → Randonnée
