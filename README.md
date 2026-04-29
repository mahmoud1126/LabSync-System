# LabSync-System
University Lab Equipment booking and management system.

## Prerequisites

[Docker](https://docs.docker.com/get-docker/)

## Installation

### Launch the project
```sh
docker-compose up -d
```

### Stopping the project

```sh
docker-compose down
```


## Common Checks

### Port conflict
Before launching the project ensure that you host machine doesn't require
those ports
- 80 for apache
- 3306 for mysql
you can check active services at those ports with

```sh
ss -tulpn | grep -E ':80|:3306'
```

### Permission Denied
If you get a "Permission Denied" when running docker
ensure your user is in the docker group
- `sudo usermod -aG docker $USER` you need to restart for this to take effect
- or you can just simply use `sudo docker-compose`
