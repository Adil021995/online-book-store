CREATE DATABASE IF NOT EXISTS online_book_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE online_book_store;

SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS order_tracking;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS addresses;
DROP TABLE IF EXISTS cart_items;
DROP TABLE IF EXISTS cart;
DROP TABLE IF EXISTS books;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS banners;
DROP TABLE IF EXISTS admins;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  phone VARCHAR(25) DEFAULT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(80) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  slug VARCHAR(120) NOT NULL UNIQUE,
  description VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB;

CREATE TABLE books (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category_id INT DEFAULT NULL,
  title VARCHAR(180) NOT NULL,
  author VARCHAR(160) NOT NULL,
  description TEXT,
  price DECIMAL(10,2) NOT NULL DEFAULT 0,
  old_price DECIMAL(10,2) DEFAULT NULL,
  cover VARCHAR(500) DEFAULT NULL,
  stock INT NOT NULL DEFAULT 0,
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE addresses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  full_name VARCHAR(120) NOT NULL,
  phone VARCHAR(25) NOT NULL,
  address_line TEXT NOT NULL,
  city VARCHAR(100) NOT NULL,
  state VARCHAR(100) NOT NULL,
  postal_code VARCHAR(20) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE cart (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE cart_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  cart_id INT NOT NULL,
  book_id INT NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  UNIQUE KEY unique_cart_book (cart_id, book_id),
  FOREIGN KEY (cart_id) REFERENCES cart(id) ON DELETE CASCADE,
  FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_number VARCHAR(40) NOT NULL UNIQUE,
  user_id INT NOT NULL,
  address_id INT DEFAULT NULL,
  total DECIMAL(10,2) NOT NULL,
  payment_method VARCHAR(40) NOT NULL DEFAULT 'cod',
  payment_status VARCHAR(40) NOT NULL DEFAULT 'pending',
  status VARCHAR(40) NOT NULL DEFAULT 'Placed',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (address_id) REFERENCES addresses(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  book_id INT DEFAULT NULL,
  book_title VARCHAR(180) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  quantity INT NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  method VARCHAR(40) NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  status VARCHAR(40) NOT NULL DEFAULT 'pending',
  transaction_reference VARCHAR(120) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE order_tracking (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  status VARCHAR(40) NOT NULL,
  note VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE banners (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(180) NOT NULL,
  subtitle VARCHAR(255) DEFAULT NULL,
  button_text VARCHAR(80) DEFAULT 'Explore books',
  button_link VARCHAR(255) DEFAULT 'index.php#books',
  background VARCHAR(40) DEFAULT '#18233d',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

INSERT INTO categories (name,slug,description) VALUES
('Fiction','fiction','Stories, novels and unforgettable worlds'),
('Thriller','thriller','Mystery, suspense and page-turners'),
('Self Growth','self-growth','Ideas for better habits and a better life'),
('Business & Finance','business-finance','Money, careers and entrepreneurship'),
('Programming','programming','Coding, software and technology'),
('Education','education','Learning resources and study guides'),
('Lifestyle','lifestyle','Wellness, culture and everyday inspiration');

INSERT INTO books (category_id,title,author,description,price,old_price,cover,stock,is_featured) VALUES
(2,'The Silent Patient','Alex Michaelides','A gripping psychological thriller full of twists and secrets.',399,499,'https://images.unsplash.com/photo-1544947950-fa07a98d237f?auto=format&fit=crop&w=700&q=85',25,1),
(3,'Atomic Habits','James Clear','A practical guide to building good habits and breaking bad ones.',499,599,'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=700&q=85',30,1),
(4,'The Psychology of Money','Morgan Housel','Timeless lessons on wealth, greed and happiness.',349,449,'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?auto=format&fit=crop&w=700&q=85',20,1),
(7,'Ikigai','Héctor García and Francesc Miralles','Discover the Japanese secret to a long and happy life.',299,399,'https://images.unsplash.com/photo-1519682337058-a94d519337bc?auto=format&fit=crop&w=700&q=85',18,1),
(5,'Clean Code','Robert C. Martin','A handbook of agile software craftsmanship.',699,799,'https://images.unsplash.com/photo-1532012197267-da84d127e765?auto=format&fit=crop&w=700&q=85',12,1),
(1,'The Alchemist','Paulo Coelho','A classic journey about dreams, courage and listening to your heart.',299,399,'https://images.unsplash.com/photo-1516979187457-637abb4f9353?auto=format&fit=crop&w=700&q=85',22,1),
(5,'Head First Java','Kathy Sierra and Bert Bates','A visual and beginner-friendly introduction to Java programming.',649,749,'https://images.unsplash.com/photo-1495446815901-a7297e633e8d?auto=format&fit=crop&w=700&q=85',15,0),
(6,'The Power of Your Subconscious Mind','Joseph Murphy','Explore how thoughts and beliefs can shape daily life.',249,349,'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=700&q=85',17,0),
(1,'It Ends with Us','Colleen Hoover','A moving contemporary novel about love and difficult choices.',399,499,'https://images.unsplash.com/photo-1544947950-fa07a98d237f?auto=format&fit=crop&w=700&q=85',14,0),
(4,'Rich Dad Poor Dad','Robert T. Kiyosaki','A popular introduction to financial literacy and mindset.',329,429,'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?auto=format&fit=crop&w=700&q=85',19,0),
(5,'Learning SQL','Alan Beaulieu','A practical introduction to querying and working with relational data.',549,649,'https://images.unsplash.com/photo-1532012197267-da84d127e765?auto=format&fit=crop&w=700&q=85',11,0),
(2,'Gone Girl','Gillian Flynn','A dark mystery about a marriage and a disappearance.',379,479,'https://images.unsplash.com/photo-1516979187457-637abb4f9353?auto=format&fit=crop&w=700&q=85',9,0);

INSERT INTO banners (title,subtitle,button_text,button_link,background,is_active,sort_order) VALUES
('Your next chapter starts here','Discover stories, skills and ideas that stay with you.','Explore books','index.php#books','#18233d',1,1),
('Big ideas. Better habits.','Find your next favourite read at a price you will love.','Shop bestsellers','index.php?featured=1#books','#35204d',1,2),
('Make room for a new world','From page-turning thrillers to practical programming guides.','Browse categories','categories.php','#173c3b',1,3);

-- Demo admin is created by setup.php on first visit with a hashed password.
