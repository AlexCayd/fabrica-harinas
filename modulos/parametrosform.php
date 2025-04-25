<!DOCTYPE html>
 <html lang="en">
 <head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>FHE | Clientes</title>
     <link rel="stylesheet" href="../styles.css">
     <link rel="stylesheet" href="../css/menu.css">
 </head>
 <body>
     <main  class="contenedor hoja">
         <header class="header">
             <h2 class="header__logo">
                 F.H. Elizondo
             </h2>
 
             <nav class="header__nav">
                 <a href="../menu.html" class="header__btn">
                     <img class="header__icono" src="../img/home.svg" alt="Home">
                     <p class="header__textoicono">Home</p>
                 </a>
 
                 <a href="../index.html" class="header__btn">
                     <img class="header__icono" src="../img/exit.svg" alt="Home">
                     <p class="header__textoicono">Salir</p>
                 </a>
             </nav>
         </header>
 
         <div class="contenedor__modulo">
             <a href="parametros.php" class="atras">Ir atrás</a>
             <h2 class="heading">Agregar Parámetro</h2>
             <form action="clientes.html" class="formulario">
                 <div class="formulario__campo">
                     <label for="nombre" class="formulario__label">Nombre</label>
                     <input type="text" class="formulario__input" placeholder="Nombre">
                 </div>

                 
                 <div class="formulario__campo">
                     <label for="categoria" class="formulario__label">Estado</label>
                     <select name="categoria" id="categoria" class="formulario__select">
                         <option value="alveografo">Alveógrafo</option>
                         <option value="farinografo">Farinógrafo</option>
                     </select>
                 </div>
 
                 <div class="formulario__campo">
                     <label for="limite_inf" class="formulario__label">Límite inferior</label>
                     <input type="number" class="formulario__input" placeholder="Límite inferior">
                 </div>

                 <div class="formulario__campo">
                     <label for="limite_sup" class="formulario__label">Límite superior</label>
                     <input type="number" class="formulario__input" placeholder="Límite inferior">
                 </div>
 
                 <input type="submit" class="formulario__submit" value="Agregar parámetro">
             </form>
         </div>
     </main>
 </body>
 </html>