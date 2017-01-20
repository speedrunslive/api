SpeedRunsLive API Codebase
==========================

The SRL API codebase uses Zend Framework 1 and Elasticsearch. The database is NOT included in the project, you will have to get it by other means.

Windows Installation
------------

0. Make sure to clone the repo inside of your /Users/ directory. This is required for volume mounting.
0. Obtain srl.sql and put it in /docker/mysql
0. Install [Docker Toolbox](https://www.docker.com/products/docker-toolbox) for Windows
0. Boot up Docker terminal
0. Java requires a lot of memory to run (for elasticsearch), so you will need to run these two commands: `docker-machine rm default` and then `docker-machine create -d virtualbox --virtualbox-memory=4096 --virtualbox-cpu-count=2 --virtualbox-disk-size=50000 default`
0. You will need to add the IP of your docker machine to your /etc/hosts file (use whatever alias you want). You can run `docker-machine ip` to retrieve it.
0. Navigate to the root of the project
0. Run `docker-compose -f docker-compose-windows.yml up -d`, for now this will only boot up the MySQL container and the Elasticsearch container until we figure out how to mount volumes through docker-compose.
0. Run `docker build -t api_php ./docker/php`
0. Run the following command, MAKE SURE YOU FIX THE PATH FOR YOUR MACHINE. This needs to be an absolute path to work on Windows(the two beginning slashes bypass the mysys path conversion). You will probably just need to replace "Jiano" and "srl": `docker run -d -v //c/Users/Jiano/srl/api:/var/www/html --network api_api --name php_api api_php`
0. Run the following command just like the previous, FIX THE PATH: `docker run -d -p 8081:80 -v //c/Users/Jiano/srl/api/docker/nginx:/etc/nginx/conf.d --network api_api --name nginx_api nginx`

Now in a browser just navigate to whatever alias you used in your /etc/hosts using the :8081 port to verify. For example, if I used "dev.speedrunslive.com" as the alias, the url would be: http://dev.speedrunslive.com:8081

Once you verify that the API is indeed working, you can now navigate to port 9200 on the same domain (http://dev.speedrunslive.com:8081) to verify that Elasticsearch is working.

After verifying that Elasticsearch is working, run this command: `docker-compose -f esjdbc.yml up` and wait for it to finish.

That completes the setup for the API.
