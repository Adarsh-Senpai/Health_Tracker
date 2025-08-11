-- Create database
CREATE DATABASE IF NOT EXISTS fitness_tracker;
USE fitness_tracker;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    height FLOAT,
    weight FLOAT,
    age INT,
    fitness_goal ENUM('weight_loss', 'muscle_gain', 'maintenance'),
    is_premium BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Food items table
CREATE TABLE IF NOT EXISTS food_items (
    food_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    calories INT NOT NULL,
    protein FLOAT,
    carbs FLOAT,
    fats FLOAT,
    serving_size VARCHAR(50)
);

-- Food logs table
CREATE TABLE IF NOT EXISTS food_logs (
    log_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    food_id INT,
    servings FLOAT,
    meal_type ENUM('breakfast', 'lunch', 'dinner', 'snack'),
    date_logged TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (food_id) REFERENCES food_items(food_id)
);

-- Workout logs table
CREATE TABLE IF NOT EXISTS workout_logs (
    workout_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    workout_type VARCHAR(50),
    duration INT,
    calories_burned INT,
    date_logged TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Sleep logs table
CREATE TABLE IF NOT EXISTS sleep_logs (
    sleep_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    sleep_start DATETIME,
    sleep_end DATETIME,
    quality ENUM('poor', 'fair', 'good', 'excellent'),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Workout plans table
CREATE TABLE IF NOT EXISTS workout_plans (
    plan_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    difficulty ENUM('beginner', 'intermediate', 'advanced'),
    description TEXT,
    duration INT
);

-- Chat history table
CREATE TABLE IF NOT EXISTS chat_history (
    chat_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    message TEXT,
    is_ai BOOLEAN,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Recipes table
CREATE TABLE IF NOT EXISTS recipes (
    recipe_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    instructions TEXT,
    prep_time INT,
    cook_time INT,
    servings INT,
    calories_per_serving INT,
    protein FLOAT,
    carbs FLOAT,
    fats FLOAT,
    image_url VARCHAR(255),
    cuisine_type VARCHAR(50),
    difficulty_level ENUM('easy', 'medium', 'hard'),
    is_vegetarian BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Recipe ingredients table
CREATE TABLE IF NOT EXISTS recipe_ingredients (
    recipe_ingredient_id INT PRIMARY KEY AUTO_INCREMENT,
    recipe_id INT,
    ingredient_name VARCHAR(100),
    amount FLOAT,
    unit VARCHAR(20),
    FOREIGN KEY (recipe_id) REFERENCES recipes(recipe_id) ON DELETE CASCADE
);

-- Meal plans table
CREATE TABLE IF NOT EXISTS meal_plans (
    plan_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    name VARCHAR(100),
    description TEXT,
    target_calories INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Meal plan items table
CREATE TABLE IF NOT EXISTS meal_plan_items (
    item_id INT PRIMARY KEY AUTO_INCREMENT,
    plan_id INT,
    recipe_id INT,
    meal_type ENUM('breakfast', 'lunch', 'dinner', 'snack'),
    day_of_week ENUM('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'),
    servings INT DEFAULT 1,
    FOREIGN KEY (plan_id) REFERENCES meal_plans(plan_id) ON DELETE CASCADE,
    FOREIGN KEY (recipe_id) REFERENCES recipes(recipe_id)
);

-- Sample food items data
INSERT INTO food_items (name, calories, protein, carbs, fats, serving_size) VALUES
-- Basic items
('Chicken Breast', 165, 31, 0, 3.6, '100g'),
('Brown Rice', 216, 4.5, 45, 1.8, '100g'),
('Banana', 89, 1.1, 23, 0.3, '100g'),
('Egg', 78, 6.3, 0.6, 5.3, '1 large'),
('Oatmeal', 307, 11, 55, 5, '100g'),

-- Indian Main Course
('Dal Makhani', 280, 9, 35, 12, '1 cup'),
('Butter Chicken', 325, 28, 8, 20, '1 cup'),
('Palak Paneer', 260, 14, 11, 18, '1 cup'),
('Chana Masala', 240, 11, 35, 8, '1 cup'),
('Vegetable Biryani', 280, 6, 45, 9, '1 cup'),
('Chicken Biryani', 310, 18, 42, 10, '1 cup'),
('Malai Kofta', 290, 8, 25, 18, '2 pieces with gravy'),
('Rajma Curry', 235, 10, 32, 7, '1 cup'),
('Aloo Gobi', 180, 5, 28, 6, '1 cup'),
('Paneer Tikka Masala', 290, 16, 12, 20, '1 cup'),

-- Indian Breads
('Roti/Chapati', 120, 3, 20, 3.5, '1 piece'),
('Naan', 260, 9, 48, 3, '1 piece'),
('Paratha', 180, 4, 25, 7, '1 piece'),
('Puri', 140, 3, 21, 5, '1 piece'),

-- Indian Breakfast Items
('Idli', 98, 4, 21, 0.1, '2 pieces'),
('Dosa', 120, 3, 20, 4, '1 regular size'),
('Sambar', 140, 6, 25, 2, '1 cup'),
('Upma', 180, 5, 30, 5, '1 cup'),
('Poha', 170, 4, 32, 3, '1 cup'),
('Vada', 160, 5, 20, 7, '2 pieces'),

-- Indian Snacks
('Samosa', 260, 6, 24, 16, '1 piece'),
('Pakora', 150, 4, 15, 9, '100g'),
('Bhel Puri', 180, 5, 30, 5, '1 cup'),
('Pani Puri', 75, 2, 12, 2, '6 pieces'),
('Dhokla', 110, 4, 18, 3, '100g'),

-- Indian Sweets
('Gulab Jamun', 175, 2, 25, 8, '2 pieces'),
('Rasgulla', 140, 4, 24, 3, '2 pieces'),
('Jalebi', 150, 2, 30, 3, '100g'),
('Kheer', 180, 4, 28, 6, '1 cup'),
('Barfi', 130, 3, 18, 5, '2 pieces'),

-- Indian Accompaniments
('Raita', 60, 3, 6, 3, '1 cup'),
('Papadum', 35, 1, 7, 0.5, '1 piece'),
('Pickle (Mixed)', 30, 0, 6, 1, '1 tablespoon'),
('Mint Chutney', 40, 1, 4, 3, '2 tablespoons'),
('Tamarind Chutney', 50, 0, 12, 0, '2 tablespoons');

-- Sample recipes data
INSERT INTO recipes (name, description, instructions, prep_time, cook_time, servings, calories_per_serving, protein, carbs, fats, cuisine_type, difficulty_level, is_vegetarian) VALUES
-- Breakfast Recipes
('Overnight Oats with Berries', 
'Healthy and filling breakfast that you can prepare the night before', 
'1. In a jar, combine oats and milk\n2. Add honey and vanilla extract\n3. Mix well and refrigerate overnight\n4. In the morning, top with fresh berries and nuts',
10, 0, 1, 350, 12, 45, 8, 'International', 'easy', TRUE),

('Masala Dosa',
'Classic South Indian crispy crepe with potato filling',
'1. Prepare dosa batter with rice and urad dal\n2. Make potato filling with spices\n3. Heat griddle and spread batter\n4. Add filling and fold\n5. Serve with chutney and sambar',
30, 15, 2, 280, 8, 45, 6, 'Indian', 'medium', TRUE),

-- Lunch/Dinner Recipes
('Chicken Tikka Masala',
'Grilled chicken in creamy tomato sauce',
'1. Marinate chicken in yogurt and spices\n2. Grill or bake chicken pieces\n3. Prepare tomato-based curry sauce\n4. Combine chicken with sauce\n5. Garnish with cream and cilantro',
20, 40, 4, 420, 28, 12, 18, 'Indian', 'medium', FALSE),

('Quinoa Buddha Bowl',
'Nutritious bowl with quinoa, roasted vegetables, and tahini dressing',
'1. Cook quinoa according to package instructions\n2. Roast mixed vegetables with olive oil\n3. Prepare tahini dressing\n4. Assemble bowl with quinoa base\n5. Top with vegetables and dressing',
15, 25, 2, 380, 15, 48, 12, 'International', 'easy', TRUE);

-- Insert recipe ingredients
INSERT INTO recipe_ingredients (recipe_id, ingredient_name, amount, unit) VALUES
-- Overnight Oats
(1, 'Rolled Oats', 0.5, 'cup'),
(1, 'Milk', 1, 'cup'),
(1, 'Honey', 1, 'tablespoon'),
(1, 'Mixed Berries', 0.5, 'cup'),
(1, 'Chia Seeds', 1, 'tablespoon'),

-- Masala Dosa
(2, 'Dosa Rice', 2, 'cups'),
(2, 'Urad Dal', 0.5, 'cup'),
(2, 'Potatoes', 3, 'medium'),
(2, 'Onions', 2, 'medium'),
(2, 'Mustard Seeds', 1, 'teaspoon'),

-- Chicken Tikka Masala
(3, 'Chicken Breast', 500, 'grams'),
(3, 'Yogurt', 1, 'cup'),
(3, 'Tomatoes', 4, 'large'),
(3, 'Heavy Cream', 0.5, 'cup'),
(3, 'Garam Masala', 2, 'tablespoons'),

-- Quinoa Buddha Bowl
(4, 'Quinoa', 1, 'cup'),
(4, 'Sweet Potato', 1, 'medium'),
(4, 'Chickpeas', 1, 'can'),
(4, 'Kale', 2, 'cups'),
(4, 'Tahini', 2, 'tablespoons');

-- Sample meal plan
INSERT INTO meal_plans (user_id, name, description, target_calories) VALUES
(1, 'Balanced Weight Loss Plan', 'A balanced 1500-calorie meal plan with mix of vegetarian and non-vegetarian options', 1500);

-- Sample meal plan items
INSERT INTO meal_plan_items (plan_id, recipe_id, meal_type, day_of_week, servings) VALUES
(1, 1, 'breakfast', 'monday', 1),
(1, 4, 'lunch', 'monday', 1),
(1, 3, 'dinner', 'monday', 1),
(1, 2, 'breakfast', 'tuesday', 1),
(1, 4, 'lunch', 'tuesday', 1),
(1, 3, 'dinner', 'tuesday', 1);

-- Additional Indian Recipes
INSERT INTO recipes (name, description, instructions, prep_time, cook_time, servings, calories_per_serving, protein, carbs, fats, cuisine_type, difficulty_level, is_vegetarian) VALUES
-- Breakfast
('Poha', 
'Light and healthy Indian breakfast made with flattened rice',
'1. Rinse poha and set aside\n2. Heat oil and add mustard seeds\n3. Add onions and curry leaves\n4. Add poha and spices\n5. Garnish with coriander and serve',
10, 15, 2, 220, 6, 40, 5, 'Indian', 'easy', TRUE),

('Sambar Idli',
'Steamed rice cakes served with lentil soup',
'1. Prepare idli batter with rice and urad dal\n2. Steam idlis in molds\n3. Prepare sambar with lentils and vegetables\n4. Serve hot idlis with sambar',
30, 20, 4, 180, 8, 32, 2, 'Indian', 'medium', TRUE),

('Aloo Paratha',
'Whole wheat flatbread stuffed with spiced potatoes',
'1. Prepare potato stuffing with spices\n2. Make whole wheat dough\n3. Stuff the dough with potato mixture\n4. Roll and cook on griddle\n5. Serve with yogurt',
25, 20, 3, 280, 7, 45, 8, 'Indian', 'medium', TRUE),

-- Main Course
('Paneer Butter Masala',
'Rich and creamy paneer curry',
'1. Prepare tomato-based gravy\n2. Add spices and cream\n3. Add paneer cubes\n4. Simmer until done\n5. Garnish with cream and serve',
20, 30, 4, 350, 16, 12, 22, 'Indian', 'medium', TRUE),

('Dal Tadka',
'Yellow lentils tempered with spices',
'1. Cook yellow lentils until soft\n2. Prepare tempering with spices\n3. Add tempering to dal\n4. Garnish with coriander',
15, 25, 4, 220, 12, 35, 5, 'Indian', 'easy', TRUE),

('Vegetable Biryani',
'Aromatic rice dish with mixed vegetables',
'1. Prepare rice and vegetables separately\n2. Layer rice and vegetables with spices\n3. Cook on dum (slow cook)\n4. Garnish with fried onions and mint',
30, 45, 4, 320, 8, 52, 10, 'Indian', 'hard', TRUE);

-- Add ingredients for new recipes
INSERT INTO recipe_ingredients (recipe_id, ingredient_name, amount, unit) VALUES
-- Poha
((SELECT recipe_id FROM recipes WHERE name = 'Poha'), 'Flattened Rice', 2, 'cups'),
((SELECT recipe_id FROM recipes WHERE name = 'Poha'), 'Onion', 1, 'medium'),
((SELECT recipe_id FROM recipes WHERE name = 'Poha'), 'Mustard Seeds', 1, 'teaspoon'),
((SELECT recipe_id FROM recipes WHERE name = 'Poha'), 'Curry Leaves', 5, 'leaves'),
((SELECT recipe_id FROM recipes WHERE name = 'Poha'), 'Green Chilies', 2, 'pieces'),
((SELECT recipe_id FROM recipes WHERE name = 'Poha'), 'Peanuts', 0.25, 'cup'),

-- Sambar Idli
((SELECT recipe_id FROM recipes WHERE name = 'Sambar Idli'), 'Idli Rice', 2, 'cups'),
((SELECT recipe_id FROM recipes WHERE name = 'Sambar Idli'), 'Urad Dal', 1, 'cup'),
((SELECT recipe_id FROM recipes WHERE name = 'Sambar Idli'), 'Toor Dal', 1, 'cup'),
((SELECT recipe_id FROM recipes WHERE name = 'Sambar Idli'), 'Mixed Vegetables', 2, 'cups'),
((SELECT recipe_id FROM recipes WHERE name = 'Sambar Idli'), 'Sambar Powder', 2, 'tablespoons'),
((SELECT recipe_id FROM recipes WHERE name = 'Sambar Idli'), 'Tamarind', 1, 'small lemon size'),

-- Aloo Paratha
((SELECT recipe_id FROM recipes WHERE name = 'Aloo Paratha'), 'Whole Wheat Flour', 2, 'cups'),
((SELECT recipe_id FROM recipes WHERE name = 'Aloo Paratha'), 'Potatoes', 3, 'medium'),
((SELECT recipe_id FROM recipes WHERE name = 'Aloo Paratha'), 'Green Chilies', 2, 'pieces'),
((SELECT recipe_id FROM recipes WHERE name = 'Aloo Paratha'), 'Garam Masala', 1, 'teaspoon'),
((SELECT recipe_id FROM recipes WHERE name = 'Aloo Paratha'), 'Ghee', 4, 'tablespoons'),

-- Paneer Butter Masala
((SELECT recipe_id FROM recipes WHERE name = 'Paneer Butter Masala'), 'Paneer', 400, 'grams'),
((SELECT recipe_id FROM recipes WHERE name = 'Paneer Butter Masala'), 'Tomatoes', 4, 'large'),
((SELECT recipe_id FROM recipes WHERE name = 'Paneer Butter Masala'), 'Cream', 200, 'ml'),
((SELECT recipe_id FROM recipes WHERE name = 'Paneer Butter Masala'), 'Butter', 50, 'grams'),
((SELECT recipe_id FROM recipes WHERE name = 'Paneer Butter Masala'), 'Cashew Nuts', 15, 'pieces'),
((SELECT recipe_id FROM recipes WHERE name = 'Paneer Butter Masala'), 'Garam Masala', 1, 'tablespoon'),

-- Dal Tadka
((SELECT recipe_id FROM recipes WHERE name = 'Dal Tadka'), 'Yellow Lentils', 2, 'cups'),
((SELECT recipe_id FROM recipes WHERE name = 'Dal Tadka'), 'Cumin Seeds', 1, 'teaspoon'),
((SELECT recipe_id FROM recipes WHERE name = 'Dal Tadka'), 'Garlic', 4, 'cloves'),
((SELECT recipe_id FROM recipes WHERE name = 'Dal Tadka'), 'Ghee', 2, 'tablespoons'),
((SELECT recipe_id FROM recipes WHERE name = 'Dal Tadka'), 'Turmeric', 0.5, 'teaspoon'),

-- Vegetable Biryani
((SELECT recipe_id FROM recipes WHERE name = 'Vegetable Biryani'), 'Basmati Rice', 2, 'cups'),
((SELECT recipe_id FROM recipes WHERE name = 'Vegetable Biryani'), 'Mixed Vegetables', 3, 'cups'),
((SELECT recipe_id FROM recipes WHERE name = 'Vegetable Biryani'), 'Biryani Masala', 2, 'tablespoons'),
((SELECT recipe_id FROM recipes WHERE name = 'Vegetable Biryani'), 'Saffron', 1, 'pinch'),
((SELECT recipe_id FROM recipes WHERE name = 'Vegetable Biryani'), 'Ghee', 4, 'tablespoons'),
((SELECT recipe_id FROM recipes WHERE name = 'Vegetable Biryani'), 'Fried Onions', 1, 'cup');

-- Add sample meal plans
INSERT INTO meal_plans (user_id, name, description, target_calories) VALUES
(1, 'Vegetarian Indian Plan', 'A balanced vegetarian meal plan featuring Indian cuisine', 1800),
(1, 'Low Calorie Indian Diet', 'Indian recipes modified for weight loss', 1500);

-- Add items to the vegetarian meal plan
INSERT INTO meal_plan_items (plan_id, recipe_id, meal_type, day_of_week, servings) VALUES
-- Monday
((SELECT plan_id FROM meal_plans WHERE name = 'Vegetarian Indian Plan'), 
 (SELECT recipe_id FROM recipes WHERE name = 'Poha'),
 'breakfast', 'monday', 1),
((SELECT plan_id FROM meal_plans WHERE name = 'Vegetarian Indian Plan'),
 (SELECT recipe_id FROM recipes WHERE name = 'Paneer Butter Masala'),
 'lunch', 'monday', 1),
((SELECT plan_id FROM meal_plans WHERE name = 'Vegetarian Indian Plan'),
 (SELECT recipe_id FROM recipes WHERE name = 'Dal Tadka'),
 'dinner', 'monday', 1),

-- Tuesday
((SELECT plan_id FROM meal_plans WHERE name = 'Vegetarian Indian Plan'),
 (SELECT recipe_id FROM recipes WHERE name = 'Sambar Idli'),
 'breakfast', 'tuesday', 1),
((SELECT plan_id FROM meal_plans WHERE name = 'Vegetarian Indian Plan'),
 (SELECT recipe_id FROM recipes WHERE name = 'Vegetable Biryani'),
 'lunch', 'tuesday', 1),
((SELECT plan_id FROM meal_plans WHERE name = 'Vegetarian Indian Plan'),
 (SELECT recipe_id FROM recipes WHERE name = 'Dal Tadka'),
 'dinner', 'tuesday', 1),

-- Wednesday
((SELECT plan_id FROM meal_plans WHERE name = 'Vegetarian Indian Plan'),
 (SELECT recipe_id FROM recipes WHERE name = 'Aloo Paratha'),
 'breakfast', 'wednesday', 1),
((SELECT plan_id FROM meal_plans WHERE name = 'Vegetarian Indian Plan'),
 (SELECT recipe_id FROM recipes WHERE name = 'Paneer Butter Masala'),
 'lunch', 'wednesday', 1),
((SELECT plan_id FROM meal_plans WHERE name = 'Vegetarian Indian Plan'),
 (SELECT recipe_id FROM recipes WHERE name = 'Vegetable Biryani'),
 'dinner', 'wednesday', 1);

-- Add 20 more healthy recipes
INSERT INTO recipes (name, description, instructions, prep_time, cook_time, servings, calories_per_serving, protein, carbs, fats, cuisine_type, difficulty_level, is_vegetarian) VALUES
-- Breakfast Options
('Greek Yogurt Parfait',
'Protein-rich breakfast with layers of yogurt, fruits, and granola',
'1. Layer Greek yogurt in a glass\n2. Add mixed berries\n3. Sprinkle with honey and granola\n4. Repeat layers\n5. Top with nuts and seeds',
5, 0, 1, 280, 15, 35, 8, 'International', 'easy', TRUE),

('Spinach and Mushroom Omelette',
'Protein-packed omelette with vegetables',
'1. Whisk eggs with salt and pepper\n2. Sauté mushrooms and spinach\n3. Pour eggs over vegetables\n4. Add cheese if desired\n5. Fold and serve',
5, 10, 1, 250, 18, 6, 16, 'International', 'easy', FALSE),

-- Salads
('Quinoa Buddha Bowl with Tahini',
'Nutrient-rich bowl with quinoa, roasted vegetables and tahini dressing',
'1. Cook quinoa\n2. Roast vegetables\n3. Prepare tahini dressing\n4. Assemble bowl\n5. Drizzle with dressing',
15, 20, 2, 380, 12, 45, 18, 'Mediterranean', 'easy', TRUE),

('Mediterranean Chickpea Salad',
'Fresh and light salad with protein-rich chickpeas',
'1. Combine chickpeas, cucumber, tomatoes\n2. Add feta and olives\n3. Mix olive oil and lemon dressing\n4. Toss together\n5. Chill before serving',
10, 0, 4, 250, 10, 30, 12, 'Mediterranean', 'easy', TRUE),

-- Indian Healthy Options
('Mixed Vegetable Curry',
'Healthy curry loaded with seasonal vegetables',
'1. Prepare curry base with onions and tomatoes\n2. Add mixed vegetables\n3. Season with spices\n4. Simmer until cooked\n5. Garnish with cilantro',
15, 25, 4, 220, 8, 28, 10, 'Indian', 'medium', TRUE),

('Grilled Chicken Tikka',
'Lean protein marinated in yogurt and spices',
'1. Marinate chicken in spiced yogurt\n2. Thread onto skewers\n3. Grill until cooked\n4. Serve with mint chutney\n5. Garnish with lemon',
20, 15, 4, 280, 32, 8, 12, 'Indian', 'medium', FALSE),

-- Asian Inspired
('Tofu Stir-Fry',
'Quick and healthy vegetarian stir-fry',
'1. Press and cube tofu\n2. Stir-fry vegetables\n3. Add tofu and sauce\n4. Cook until heated\n5. Serve with brown rice',
15, 15, 4, 260, 15, 25, 12, 'Asian', 'easy', TRUE),

('Salmon Teriyaki',
'Omega-3 rich salmon with homemade teriyaki sauce',
'1. Prepare teriyaki sauce\n2. Marinate salmon\n3. Grill or bake salmon\n4. Brush with sauce\n5. Serve with vegetables',
10, 20, 4, 320, 28, 15, 18, 'Asian', 'medium', FALSE),

-- Mediterranean
('Grilled Mediterranean Vegetables',
'Colorful grilled vegetables with herbs',
'1. Slice vegetables\n2. Toss with olive oil and herbs\n3. Grill until tender\n4. Drizzle with balsamic\n5. Serve warm or cold',
15, 20, 4, 180, 5, 25, 8, 'Mediterranean', 'easy', TRUE),

('Baked Falafel',
'Healthy baked version of traditional falafel',
'1. Process chickpeas and herbs\n2. Form into balls\n3. Brush with oil\n4. Bake until crispy\n5. Serve with tahini sauce',
20, 25, 4, 240, 12, 35, 8, 'Mediterranean', 'medium', TRUE),

-- Soups and Stews
('Lentil Soup',
'Hearty and protein-rich soup',
'1. Sauté vegetables\n2. Add lentils and broth\n3. Season with spices\n4. Simmer until cooked\n5. Garnish with parsley',
10, 30, 6, 200, 12, 32, 2, 'International', 'easy', TRUE),

('Chicken Vegetable Soup',
'Light and nutritious soup',
'1. Cook chicken in broth\n2. Add vegetables\n3. Season with herbs\n4. Simmer until done\n5. Add fresh herbs',
15, 35, 6, 180, 18, 15, 6, 'International', 'easy', FALSE),

-- Healthy Snacks
('Roasted Chickpeas',
'Crunchy and protein-rich snack',
'1. Drain and dry chickpeas\n2. Toss with oil and spices\n3. Spread on baking sheet\n4. Roast until crispy\n5. Cool before serving',
5, 35, 4, 150, 8, 25, 5, 'International', 'easy', TRUE),

('Energy Balls',
'Natural energy boosters with dates and nuts',
'1. Process dates and nuts\n2. Add oats and honey\n3. Form into balls\n4. Roll in coconut\n5. Chill before serving',
15, 0, 12, 120, 3, 15, 7, 'International', 'easy', TRUE),

-- Light Dinners
('Zucchini Noodles with Pesto',
'Low-carb alternative to pasta',
'1. Spiralize zucchini\n2. Prepare fresh pesto\n3. Toss noodles with pesto\n4. Add cherry tomatoes\n5. Garnish with pine nuts',
15, 5, 2, 180, 6, 8, 15, 'Italian', 'easy', TRUE),

('Grilled Fish Tacos',
'Healthy tacos with grilled fish and slaw',
'1. Season fish\n2. Prepare slaw\n3. Grill fish\n4. Warm tortillas\n5. Assemble tacos',
20, 10, 4, 280, 24, 25, 12, 'Mexican', 'medium', FALSE),

-- Healthy Desserts
('Chia Seed Pudding',
'Nutritious dessert with chia seeds and fruit',
'1. Mix chia seeds with milk\n2. Add honey and vanilla\n3. Refrigerate overnight\n4. Top with fruits\n5. Add nuts if desired',
5, 0, 2, 160, 6, 20, 8, 'International', 'easy', TRUE),

('Baked Apple with Cinnamon',
'Warm and comforting healthy dessert',
'1. Core apples\n2. Fill with oats and cinnamon\n3. Add honey\n4. Bake until tender\n5. Serve warm',
10, 30, 4, 120, 1, 28, 1, 'International', 'easy', TRUE),

-- Breakfast Bowls
('Acai Bowl',
'Antioxidant-rich breakfast bowl',
'1. Blend acai with fruits\n2. Pour into bowl\n3. Top with granola\n4. Add fresh fruits\n5. Drizzle with honey',
10, 0, 1, 320, 8, 45, 12, 'Brazilian', 'easy', TRUE),

('Savory Oatmeal Bowl',
'Healthy savory twist on traditional oatmeal',
'1. Cook steel-cut oats\n2. Add sautéed vegetables\n3. Top with poached egg\n4. Season with herbs\n5. Add hot sauce if desired',
5, 20, 1, 290, 14, 40, 8, 'International', 'medium', FALSE);

-- Add ingredients for new recipes
INSERT INTO recipe_ingredients (recipe_id, ingredient_name, amount, unit) VALUES
-- Greek Yogurt Parfait
((SELECT recipe_id FROM recipes WHERE name = 'Greek Yogurt Parfait'), 'Greek Yogurt', 1, 'cup'),
((SELECT recipe_id FROM recipes WHERE name = 'Greek Yogurt Parfait'), 'Mixed Berries', 1, 'cup'),
((SELECT recipe_id FROM recipes WHERE name = 'Greek Yogurt Parfait'), 'Granola', 0.25, 'cup'),
((SELECT recipe_id FROM recipes WHERE name = 'Greek Yogurt Parfait'), 'Honey', 1, 'tablespoon'),

-- Spinach and Mushroom Omelette
((SELECT recipe_id FROM recipes WHERE name = 'Spinach and Mushroom Omelette'), 'Eggs', 3, 'large'),
((SELECT recipe_id FROM recipes WHERE name = 'Spinach and Mushroom Omelette'), 'Spinach', 1, 'cup'),
((SELECT recipe_id FROM recipes WHERE name = 'Spinach and Mushroom Omelette'), 'Mushrooms', 0.5, 'cup'),
((SELECT recipe_id FROM recipes WHERE name = 'Spinach and Mushroom Omelette'), 'Olive Oil', 1, 'teaspoon'),

-- Mediterranean Chickpea Salad
((SELECT recipe_id FROM recipes WHERE name = 'Mediterranean Chickpea Salad'), 'Chickpeas', 2, 'cups'),
((SELECT recipe_id FROM recipes WHERE name = 'Mediterranean Chickpea Salad'), 'Cucumber', 1, 'medium'),
((SELECT recipe_id FROM recipes WHERE name = 'Mediterranean Chickpea Salad'), 'Cherry Tomatoes', 1, 'cup'),
((SELECT recipe_id FROM recipes WHERE name = 'Mediterranean Chickpea Salad'), 'Feta Cheese', 0.5, 'cup'),

-- Grilled Chicken Tikka
((SELECT recipe_id FROM recipes WHERE name = 'Grilled Chicken Tikka'), 'Chicken Breast', 500, 'grams'),
((SELECT recipe_id FROM recipes WHERE name = 'Grilled Chicken Tikka'), 'Yogurt', 1, 'cup'),
((SELECT recipe_id FROM recipes WHERE name = 'Grilled Chicken Tikka'), 'Tikka Masala', 2, 'tablespoons'),
((SELECT recipe_id FROM recipes WHERE name = 'Grilled Chicken Tikka'), 'Lemon', 1, 'whole'),

-- Salmon Teriyaki
((SELECT recipe_id FROM recipes WHERE name = 'Salmon Teriyaki'), 'Salmon Fillet', 600, 'grams'),
((SELECT recipe_id FROM recipes WHERE name = 'Salmon Teriyaki'), 'Soy Sauce', 0.25, 'cup'),
((SELECT recipe_id FROM recipes WHERE name = 'Salmon Teriyaki'), 'Mirin', 2, 'tablespoons'),
((SELECT recipe_id FROM recipes WHERE name = 'Salmon Teriyaki'), 'Ginger', 1, 'tablespoon'),

-- Zucchini Noodles with Pesto
((SELECT recipe_id FROM recipes WHERE name = 'Zucchini Noodles with Pesto'), 'Zucchini', 2, 'large'),
((SELECT recipe_id FROM recipes WHERE name = 'Zucchini Noodles with Pesto'), 'Fresh Basil', 2, 'cups'),
((SELECT recipe_id FROM recipes WHERE name = 'Zucchini Noodles with Pesto'), 'Pine Nuts', 0.25, 'cup'),
((SELECT recipe_id FROM recipes WHERE name = 'Zucchini Noodles with Pesto'), 'Olive Oil', 0.25, 'cup'),

-- Chia Seed Pudding
((SELECT recipe_id FROM recipes WHERE name = 'Chia Seed Pudding'), 'Chia Seeds', 0.25, 'cup'),
((SELECT recipe_id FROM recipes WHERE name = 'Chia Seed Pudding'), 'Almond Milk', 1, 'cup'),
((SELECT recipe_id FROM recipes WHERE name = 'Chia Seed Pudding'), 'Honey', 1, 'tablespoon'),
((SELECT recipe_id FROM recipes WHERE name = 'Chia Seed Pudding'), 'Vanilla Extract', 0.5, 'teaspoon');

-- Add these recipes to the sample meal plan
INSERT INTO meal_plan_items (plan_id, recipe_id, meal_type, day_of_week, servings) VALUES
((SELECT plan_id FROM meal_plans WHERE name = 'Vegetarian Indian Plan'),
 (SELECT recipe_id FROM recipes WHERE name = 'Greek Yogurt Parfait'),
 'breakfast', 'thursday', 1),
((SELECT plan_id FROM meal_plans WHERE name = 'Vegetarian Indian Plan'),
 (SELECT recipe_id FROM recipes WHERE name = 'Mediterranean Chickpea Salad'),
 'lunch', 'thursday', 1),
((SELECT plan_id FROM meal_plans WHERE name = 'Vegetarian Indian Plan'),
 (SELECT recipe_id FROM recipes WHERE name = 'Zucchini Noodles with Pesto'),
 'dinner', 'thursday', 1); 