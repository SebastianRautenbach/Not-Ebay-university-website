
<html>
  <head>
  </head>

  <body> <!-- We fake a search just by changing the URL staticly -->
    <div class="container my-2 p-2 ">
      <div class="input-group mb-3">
        <form action="index.php" method="GET" class="d-flex w-100">
          <input type="hidden" name="q" value="listings">
          <input type="text" name="category" class="form-control" placeholder="Search for anything" required /> <!-- Copied this from ebay just for fun -->
          <div class="input-group-append">
            <button class="btn btn-primary" type="submit">Search</button>
          </div>
        </form>
      </div>
    </div>

  </body>

</html>


