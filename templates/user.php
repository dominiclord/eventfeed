<!DOCTYPE html>
<html xmlns='http://www.w3.org/1999/xhtml' lang='en'>
    <head>
        <meta charset='utf-8'/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
        <title>Soumettre un message - Technosoirée</title>
        <link rel="stylesheet" href="utilisateur/styles.css" media="all">
    </head>
    <body>
        <div id="overlay"></div>
        <h1>Technosoirée</h1>
<?php
    if ($_SERVER['QUERY_STRING']=="succes"){
?>
        <section style="display:block;" id="container-succes">
            <h3>Merci! Votre message a été envoyé.</h3>
            <h3>Il passera à travers un processus de validation et s'affichera bientôt lorsqu'il sera approuvé!</h3>
            <h3>Bonne soirée!</h3>
            <h3><a href="index.php">&laquo;Retour</a></h3>
        </section>
<?php
    }else{
?>
        <form action="/submit" enctype="multipart/form-data" id="formEntry" method="post">
            <ul>
                <li>
                    <label for="author">Votre nom (obligatoire) :</label>
                    <input id="author" type="text" name="author" value="">
                </li>
                <li>
                    <label for="text">Votre message :</label>
                    <input id="text" type="text" name="text" value="">
                </li>
                <li>
                    <label for="imagefile">Image :</label>
                    <input id="imagefile" type="file" accept="image/*" name="files" data-url="server/php/">
                </li>
            </ul>
            <div id="progress">
                <div class="bar" style="width: 0%;"></div>
            </div>
            <p style="display:none;" id="nocontent"><strong>Il vous faut envoyer au minimum un message ou une image.</strong></p>
            <p>Vous pouvez envoyer un message, une image ou les deux en même temps.</p>
            <p>Les contenus jugés innapproprié ne seront pas publiés.</p>
            <input id="btnCancel" type="reset" value="Erase">
            <input id="btnSubmit" name="btnSubmit" type="submit" value="Send">
            <input id="state" name="state" type="hidden" value="Send">
            <input id="timestamp" type="hidden" value="">
            <input id="image" type="hidden" value="">
        </form>
<?php
    }
?>
        <script src="utilisateur/jquery-1.8.0.min.js"></script>
        <script src="utilisateur/scripts.js"></script>
    </body>
</html>