# Web prodavnica recepata - mikroservisna aplikacija

Aplikacija je podeljena na nezavisne mikroservise i koristi API Gateway kao jedinstvenu ulaznu tačku za frontend. Sistem omogućava registraciju i prijavu korisnika, pregled sastojaka i recepata, dodavanje stavki u korpu i kreiranje narudžbina.

## Arhitektura

Komponente sistema:

- **api-gateway** - jedinstvena ulazna tačka na `http://localhost:8000/api`; prosleđuje zahteve ka internim servisima i primenjuje CORS, CSRF i sigurnosne HTTP headere.
- **user-service** - autentifikacija, korisnici i role; koristi zasebnu MySQL bazu `users_db`.
- **catalog-service** - recepti, sastojci i zalihe; koristi zasebnu MongoDB bazu `catalog_db`.
- **ordering-service** - korpa, narudžbine i plaćena porudžbina; koristi zasebnu MySQL bazu `orders_db`.
- **frontend** - React/Vite korisnički interfejs na `http://localhost:5173`.
- **Kafka + Zookeeper** - asinhrona komunikacija između servisa.
- **Prometheus + Grafana** - monitoring i vizualizacija metrika.

Svaki servis ima sopstveni Dockerfile i sopstvenu bazu podataka.

## Eksterni API servisi

Catalog service koristi dva eksterna izvora za recepte:

1. **TheMealDB** - javna pretraga recepata po nazivu.
2. **Spoonacular** - pretraga recepata preko API key-a. API key se ne verzionise u repozitorijumu, vec se unosi kroz `.env` fajl.

Ruta preko gateway-a:

```bash
GET http://localhost:8000/api/catalog/public/recipes?q=chicken&source=both&limit=10
```

Dozvoljene vrednosti za `source` su: `mealdb`, `spoonacular`, `both`.

U `catalog-service/.env` dodati:

```env
SPOONACULAR_API_KEY=spoonacular_api_key
```

## Kafka Event-Driven Architecture

Definisane Kafka teme:

| Topic | Producer | Consumer | Svrha |
|---|---|---|---|
| `order-created` | ordering-service | catalog-service | Kreirana je narudžbina i potrebno je proveriti/dekrementirati zalihe. |
| `inventory-updated` | catalog-service | ordering-service | Zalihe su uspešno ažurirane i narudžbina može preći u status `isporučeno`. |
| `greska-pri-obradi` | catalog-service | ordering-service | Zaliha nema dovoljno ili sastojak ne postoji, pa se narudžbina otkazuje. |

`catalog-service` je hibridni processor: sluša `order-created`, izvršava poslovnu logiku provere zaliha, pa publikuje `inventory-updated` ili `greska-pri-obradi`.

## Distribuirani patern

Implementiran je **Circuit Breaker** u `ordering-service/app/Services/CircuitBreaker.php`. Koristi se pri pozivima ka Catalog servisu. Ako Catalog servis više puta zakaže ili je prespor, circuit breaker prelazi u `open` stanje i vraća kontrolisani fallback umesto da dozvoli kaskadni pad sistema.

## Bezbednost

Implementirani mehanizmi:

- **XSS** - sanitizacija tekstualnih ulaza u servisima i sigurnosni headeri u API Gateway-u.
- **CSRF** - API Gateway izdaje CSRF token preko `/api/csrf-token`; frontend šalje `X-CSRF-TOKEN` za zahteve koji menjaju stanje.
- **IDOR** - Ordering service proverava da korisnik vidi samo svoje narudžbine, osim ako je admin.
- **CORS** - API Gateway dozvoljava samo frontend origin-e definisane u `api-gateway/config/cors.php`.
- **SQL Injection** - Laravel ORM/Eloquent i validacija zahteva se koriste umesto ručnog spajanja SQL upita.

## Monitoring

Prometheus prikuplja metrike sa:

- `user-service:80/api/metrics`
- `catalog-service:80/api/metrics`
- `ordering-service:80/api/metrics`

Adrese:

- Prometheus: `http://localhost:9090`
- Grafana: `http://localhost:3001`
- Grafana login: `admin / admin`

U Grafani dodati Prometheus datasource sa URL-om:

```text
http://prometheus:9090
```

Primeri korisnih panela:

- `user_service_users_total`
- `user_service_admins_total`
- `catalog_service_ingredients_total`
- `ordering_service_orders_total`
- `ordering_service_memory_bytes`

## Preduslovi

Potrebno je instalirati:

- Docker Desktop
- Docker Compose
- Git

## Pokretanje lokalno

### 1. Kloniranje repozitorijuma

```bash
git clone <URL_REPOZITORIJUMA>
cd <NAZIV_REPOZITORIJUMA>
```

### 2. Pokretanje infrastrukture i aplikacije

```bash
docker compose up --build -d
```

### 3. Migracije za SQL servise

```bash
docker compose exec user-service php artisan migrate --force
docker compose exec ordering-service php artisan migrate --force
```

Catalog service koristi MongoDB, pa za osnovni rad nije potrebna SQL migracija za catalog bazu.

### 4. Provera servisa

```bash
curl http://localhost:8000/api/catalog/ingredients
curl http://localhost:8000/api/catalog/public/recipes?q=chicken&source=both&limit=5
```

Frontend se otvara na:

```text
http://localhost:5173
```

API Gateway je na:

```text
http://localhost:8000/api
```

Kafka UI je na:

```text
http://localhost:8085
```

## CI/CD

GitHub Actions tokovi:

- `.github/workflows/ci.yml` - pokreće testove za Laravel servise, build frontend aplikacije i Docker build za sve komponente.
- `.github/workflows/cd.yml` - na tag `v*` objavljuje Docker image-e na Docker Hub.

Potrebni GitHub secrets:

```text
DOCKERHUB_USERNAME
DOCKERHUB_TOKEN
```

## Napomena o ručnom testiranju Kafka toka

Nakon kreiranja narudžbine, proveriti logove:

```bash
docker compose logs -f ordering-service
docker compose logs -f ordering-consumer
docker compose logs -f catalog-consumer
```

Očekivani tok:

1. `ordering-service` kreira narudžbinu i publikuje `order-created`.
2. `catalog-consumer` prima događaj, proverava zalihe i publikuje `inventory-updated` ili `greska-pri-obradi`.
3. `ordering-consumer` prima rezultat i ažurira status narudžbine.
