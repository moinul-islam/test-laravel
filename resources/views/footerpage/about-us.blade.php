@extends('frontend.master')
@section('main-content')
<div class="py-4 ms-3 me-3">
<div class="container mt-4">
    <h1>About Us</h1>
   <p>
    The NIOM IT family created the eINFO app to help local people navigate business information, stay updated with local news, and access local online marketplaces.
    </p>

  <section id="contact-us">
        <h2>Contact Us</h2>
      <p>If you have any questions or suggestions about our Privacy Policy, do not hesitate to contact us at:</p>
      <p>
        <strong>NIOM IT</strong><br />
        House #3/1 (3rd Floor), Block-A, New Chashara Road, Narayanganj<br />
        Narayanganj-1400.<br />
        Email: mail@niomitbd.com<br />
        Phone: +880 18 7575 0099
      </p>
  </section>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const hash = window.location.hash;

        if (hash === "#contact-us") {
            const contactSection = document.querySelector("#contact-us");
            if (contactSection) {
                contactSection.style.backgroundColor = "#faeb87ff";
            }
        }
    });

    document.addEventListener("DOMContentLoaded", function () {
        const contactSection = document.querySelector("#contact-us");

        function updateBackground() {
            const hash = window.location.hash;
            if (hash === "#contact-us") {
                contactSection.style.backgroundColor = "#faeb87ff";
            } else {
                contactSection.style.backgroundColor = "transparent";
            }
        }

        updateBackground();

        window.addEventListener("hashchange", updateBackground);
    });
</script>
</div>
@endsection