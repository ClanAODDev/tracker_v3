CREATE DATABASE IF NOT EXISTS tracker_test;
GRANT ALL PRIVILEGES ON tracker_test.* TO 'sail'@'%';
GRANT ALL PRIVILEGES ON `tracker_test_test_%`.* TO 'sail'@'%';
