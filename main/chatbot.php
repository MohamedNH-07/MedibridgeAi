<?php

require_once __DIR__ . '/includes/app.php';
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>AI Health Assistant | MediBridge AI</title>
    <link rel="stylesheet" href="../style.css" />
    <script src="app.js" defer></script>
  </head>
  <body data-page="assistant">
    <?php render_nav('assistant'); ?>

    <main class="page-shell">
      <section class="assistant-layout">
        <div class="page-intro">
          <p class="eyebrow">AI assistant</p>
          <h1>Prepare your symptoms before care</h1>
          <p>
            Share symptoms, duration, and severity. The assistant gives general
            guidance and logs the exchange to the database for continuity.
          </p>
          <div class="quick-prompts">
            <button type="button" data-prompt="I have fever and sore throat">Fever</button>
            <button type="button" data-prompt="I have chest pain">Chest pain</button>
            <button type="button" data-prompt="I have skin rash and itching">Skin rash</button>
            <button type="button" data-prompt="I have a severe headache">Headache</button>
          </div>
          <div class="notice-box">
            <strong>Medical safety</strong>
            This assistant is not a diagnosis tool. For severe or urgent
            symptoms, seek emergency medical care immediately.
          </div>
        </div>

        <div class="chat-container">
          <div class="chat-header">
            <span>MediBridge AI Assistant</span>
            <a href="appointment.php">Book care</a>
          </div>
          <div class="chat-messages" id="chatMessages">
            <div class="chat-msg bot">
              Hello. Tell me your symptoms, how long you have had them, and your age if you are comfortable sharing it.
            </div>
          </div>
          <form class="chat-input-area" id="chatForm">
            <input id="chatInput" type="text" placeholder="Type your symptoms..." autocomplete="off" required />
            <button type="submit">Send</button>
          </form>
        </div>
      </section>
    </main>
  </body>
</html>
