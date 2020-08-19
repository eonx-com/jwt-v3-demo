# JWT v3 Demo

## Intro
This is a simple demonstration of generating and logging in to EonX rewards using a JWT v3.  This code is not production ready and is for testing purposes only.

## About
This code will create an AuthToken with random user data, it will then exchange the AuthToken for an AccessToken and redirect the browser to EonX Rewards.  

## Prerequisites
 - docker

## Gettting Started
 
 ```shell script
cp config.php.example config.php    
docker build . -t jwt-v3-demo    
``` 

update config.php with supplied credentials

```shell script
docker run -it --rm -v $(pwd)/config.php:/var/www/config.php -p 8080:8000 jwt-v3-demo
```
 
Visit `localhost:8080` in your favourite browser and click "Login".
