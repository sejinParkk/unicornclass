start:
	docker-compose up -d
	cmd //c start http://localhost:8080

stop:
	docker-compose down

restart:
	docker-compose down && docker-compose up -d

bash:
	docker exec -it unicornclass_php bash

db:
	docker exec -it unicornclass_db mariadb -u unicorn -punicorn1234 unicornclass

logs:
	docker-compose logs -f php

ps:
	docker-compose ps
