FROM wordpress:latest

# Install nginx and other dependencies
RUN apt-get update && apt-get install -y \
  git \
  openssh-client \
  && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN pecl install xdebug && \
  docker-php-ext-enable xdebug

# Add this line to suppress Apache ServerName warning
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

