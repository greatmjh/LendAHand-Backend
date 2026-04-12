# Repo structure and dev instructions
- `api` folder: Where the endpoint .php files are
- `internal-src` folder: Where .php files with common functions that are used by the endpoints are
- `examples` folder: Examples of how we should do things. These files aren't supposed to be used or referenced in the final project.
- We will use the PHP *PDO* API to access the database.
## Important: constants file
- There is a file called `internal-src/constants.php.default`. Before doing anything, you must copy/paste the file to `constants.php` and set the constants such as database IP and password. This new `constants.php` file will not be synchronised on git.

# Requirements and setup
In order for this to work, you need, at minimum
- A PHP installation which has been configured to have postgres support enabled
- A postgres server which you can connect to with an IP, port, username, password, and database name

# How to run the PHP files
- For simple files, like `examples/db_test.php`, you can run it simply by running `php examples/db_test.php` on your shell
- To run a web server to test the API, you can run `php -S 127.0.0.1:8000` and then access the php files in a web browser with the URL, for example: http://127.0.0.1:8000/examples/db_test.php