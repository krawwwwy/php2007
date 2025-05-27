-- Таблица стоматологических услуг
CREATE TABLE IF NOT EXISTS services (
  id int(11) NOT NULL AUTO_INCREMENT,
  category_id smallint(6) NOT NULL,
  name varchar(255) NOT NULL,
  alias varchar(255) NOT NULL,
  short_description text NOT NULL,
  description text NOT NULL,
  price decimal(20,2) NOT NULL,
  image varchar(255) NOT NULL DEFAULT '',
  available smallint(1) NOT NULL DEFAULT '1',
  meta_keywords varchar(255) NOT NULL DEFAULT '',
  meta_description varchar(255) NOT NULL DEFAULT '',
  meta_title varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (id),
  UNIQUE KEY id (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

-- Таблица свойств услуг
CREATE TABLE IF NOT EXISTS service_properties (
  id int(11) NOT NULL AUTO_INCREMENT,
  service_id int(11) NOT NULL,
  property_name varchar(255) NOT NULL,
  property_value varchar(255) NOT NULL,
  property_price decimal(20,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (id),
  UNIQUE KEY id (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

-- Таблица изображений услуг
CREATE TABLE IF NOT EXISTS service_images (
  id int(11) NOT NULL AUTO_INCREMENT,
  service_id int(11) NOT NULL,
  image varchar(255) NOT NULL,
  title varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (id),
  UNIQUE KEY id (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1; 