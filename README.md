# DrugCardTestTask

## Description

This is a Symfony application that parses product pages from an e-commerce site, saving data to a MySQL database and a CSV file. It includes a REST API for viewing the list of products.

## Installation and Setup

1. Clone the repository:

   ```bash
   git clone git@github.com:Atik31N/DrugCardTestTask.git
   cd DrugCardTestTask
2. Start the Docker containers:

    ```
   docker-compose up --build
3. Inside the container, install the dependencies:

    ```
    composer install

## Available Commands

```
bin/console app:parse-products
```
This command runs the parser, which collects data from the site and saves it to the database and CSV file.

## Available Endpoints

```
https://localhost/api/products
```
Returns a list of products and their count in JSON format.
