import React from "react";
import "../styles/home.css";

function HomePage() {
  return (
    <div className="home">
  
      <header className="home__hero">
        <h1 className="home__title">Planiraj obroke. Izaberi recepte. Poruči sastojke.</h1>
        <p className="home__subtitle">
          Aplikacija spaja recepte i sastojke na jednom mestu: pregledaj recepte, filtriraj sastojke,
          popuni korpu i kreiraj porudžbinu za nekoliko klikova.
        </p>

        <div className="home__badges">
          <span className="home__badge">Recepti</span>
          <span className="home__badge">Sastojci</span>
          <span className="home__badge">Korpa</span>
          <span className="home__badge">Porudžbine</span>
          <span className="home__badge">Recepti iz zajednice</span>
        </div>
      </header>

      <section className="home__section">
        <h2 className="home__h2">Kako funkcioniše</h2>

        <div className="home__steps">
          <article className="home__card">
            <h3 className="home__h3">1) Pronađi recept</h3>
            <p className="home__p">
              Pregledaj interne recepte ili potraži inspiraciju kroz „Recepte iz zajednice“.
              Otvori detalje recepta i pogledaj sastojke i količine.
            </p>
          </article>

          <article className="home__card">
            <h3 className="home__h3">2) Izaberi sastojke</h3>
            <p className="home__p">
              Filtriraj sastojke po nazivu, jedinici i ceni. Sortiraj listu i brzo pronađi ono što ti treba.
              Ulogovani korisnici mogu dodati u korpu jednim klikom.
            </p>
          </article>

          <article className="home__card">
            <h3 className="home__h3">3) Kreiraj porudžbinu</h3>
            <p className="home__p">
              U korpi vidi sve stavke, izmeni količine i potvrdi kupovinu. Sistem generiše porudžbinu nakon čega možete pratiti njen status.
            </p>
          </article>
        </div>
      </section>

      <section className="home__section">
        <h2 className="home__h2">Šta možeš da radiš u aplikaciji</h2>

        <div className="home__grid">
          <div className="home__feature">
            <h3 className="home__h3">Pregled sastojaka</h3>
            <ul className="home__list">
              <li>Pretraga i filtriranje (naziv, jedinica, cena)</li>
              <li>Detalji sastojka</li>
              <li>Dodavanje u korpu</li>
            </ul>
          </div>

          <div className="home__feature">
            <h3 className="home__h3">Recepti</h3>
            <ul className="home__list">
              <li>Pretraga recepta</li>
              <li>Detalji recepta sa listom sastojaka</li>
              <li>Dodavanje sastojaka izabranog recepta u korpu</li>
            </ul>
          </div>

          <div className="home__feature">
            <h3 className="home__h3">Recepti iz zajednice</h3>
            <ul className="home__list">
              <li>Pretraga preko javnih izvora</li>
              <li>Detaljan prikaz</li>
              <li>Javno dostupno i bez prijave</li>
            </ul>
          </div>

          <div className="home__feature">
            <h3 className="home__h3">Uloge i dozvole</h3>
            <ul className="home__list">
              <li>Gost: pregled i filtriranje</li>
              <li>Korisnik: korpa + porudžbine</li>
              <li>Admin: upravljanje resursima i statusima</li>
            </ul>
          </div>
        </div>
      </section>

      <section className="home__callout">
        <h2 className="home__h2">Preporuka</h2>
        <p className="home__p">
          Ako si prvi put ovde, kreni od stranice „Sastojci“ da vidiš ponudu, ako te zanimaju recepti otvori stranicu „Recepti“
          i pogledaj detalje recepta. Za kupovinu i porudžbine potrebna je prijava.
        </p>
      </section>

      <footer className="home__foot">
        <div className="wrap">
          <p className="muted">
            Napomena: Prikaz korpe i porudžbina je dostupan samo ulogovanim korisnicima, dok su sastojci i recepti dostupni svima.
          </p>

          <p className="muted">
            © 2026 E-pijaca — Vanja i Jovana
            <span className="heart" aria-hidden="true">♥</span>
            <span className="sr-only">srce</span>
          </p>
        </div>
      </footer>
    </div>
  );
}

export default HomePage;
