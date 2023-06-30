<?
    include ($_SERVER["DOCUMENT_ROOT"] . "/inc/param.php");
            $sql = "select * from profils_recherches";
            $result = $db->query($sql);
            $noms = array();
            $sports = array();
            $categories = array();
            $tailles = array();
            $marques = array();
            $etats = array(); 
            $prix = array();
            $genre = array();
            if ($result['nbr']>0) {
                foreach ($result['tab'] as $results) {
                  $json = json_decode($results['recherche_description_json']);
                  array_push($noms,$json->nom);
                  array_push($sports,$json->sports);
                  array_push($categories,$json->categ);
                  array_push($tailles,$json->tailles);
                  array_push($marques,$json->marques);
                  array_push($etats,$json->etats);
                  array_push($prix,$json->prix);  
                  array_push($genre,$json->genre);  
                }
            } else {
                echo "Aucun utilisateur trouvé.";
            }
                $countf = 0;
                $countm = 0;
                $counte = 0;
                $count_eg = 0;
                $count_ef = 0;
                $count_em = 0;
                $count_mixte = 0;
                foreach ($categories as $item) {
                    $categories= array_filter($categories, function($value) {
                        return $value !== "";
                    });
                    if (strpos($item, "Femme") !== false) {
                        $countf++;
                    }
                    if (strpos($item, "Homme") !== false) {
                        $countm++;
                    }
                    if (strpos($item, "Equipements") !== false) {
                        $counte++;
                    }
                    if (strpos($item, "Mixte") !== false) {
                        $count_mixte ++;
                    }
                    if (strpos($item, "Enfant garçon") !== false) {
                        $count_eg++;
                    }
                    if (strpos($item, "Enfant fille") !== false) {
                        $count_ef++;
                    }
                    if (strpos($item, "Enfant Mixte") !== false) {
                        $count_em++;
                    }
                    if($item == 'Femme' or $item == 'Homme'){
                        unset($categories[array_search($item, $categories)]);
                    }     
              }

              $sql = "select * from sports s, sports_descriptions d WHERE s.sport_num = d.sport_num AND s.sport_visible = 1 AND d.langue_num = 1";
              $result = $db->query($sql);
              $sports_chart = array();
              foreach ($result['tab'] as $item) {
                  $sport = array('titre' => $item['sport_nom'], 'nbr' => 0);
                  array_push($sports_chart, $sport); 
              }
              foreach ($sports as $item2) {
                  if (strpos($item2, "Tous les sports") !== false) {
                      unset($sports[array_search($item2, $sports)]);
                  } elseif ($item2 == '') {
                      unset($sports[array_search($item2, $sports)]);
                  } else {
                      $found = false;
                      foreach ($sports_chart as &$sport) {
                          if ($sport['titre'] == $item2) {
                              $sport['nbr'] += 1;
                              $found = true;
                              break;
                          }
                      }
                      if (!$found) {
                          $item3 = array('titre' => $item2, 'nbr' => 1);
                          array_push($sports_chart, $item3);
                      }
                  }
              }

         echo "
          <script type='text/javascript' src='https://www.gstatic.com/charts/loader.js'></script>
          <script type='text/javascript'>
         google.charts.load('current', {'packages':['corechart']});
         google.charts.setOnLoadCallback(drawChart2);
         function drawChart2() {
           var data = google.visualization.arrayToDataTable([
             ['Cible', 'Catégorie les plus recherchées'],"; 
             foreach($sports_chart as $var){
              echo '["'.$var["titre"].'",'.$var["nbr"].'],';
             }
            echo "]);";
           
   
           echo "var options = {
             title: 'Sport les plus recherchées :',
             is3D: true,
           };

           var chart = new google.visualization.PieChart(document.getElementById('piechart2'));

           chart.draw(data, options);
         }
         </script>
         ";     




        echo "   <div class='forum-post'>
                <form method='post'>
                  <h1>Start date:</h1>
                  <input type='date' name = 'start_date' required>
                  <h1>End date:</h1>
                  <input type='date' name = 'end_date' required>
                  </br></br>
                  <input type='submit' class='reply-button' name = 'submit'>
              </form>
              </div>";

        if(isset($_POST['submit'])){
            $start_date = $_POST['start_date'];
            $end_date = $_POST['end_date'];
            $sql = "select  DISTINCT(s.sport_num), sport_nom from profils_sports s, sports_descriptions d WHERE s.sport_num = d.sport_num AND langue_num = 1 AND date_insert BETWEEN '$start_date' AND '$end_date' ORDER BY s.sport_num ASC";
            $result_fav = $db->query($sql);
            $sports_fav = array();
            foreach ($result_fav['tab'] as $item) {
                $sql = "select  COUNT(s.sport_num) val from profils_sports s WHERE s.sport_num = ".$item['sport_num']." AND date_insert BETWEEN '2023-05-01' AND '2023-06-28'";
                $result_count = $db->query($sql);
                $sport_count = array('titre' => $item['sport_nom'], 'sport_num' => $item['sport_num'], 'nbr' => $result_count['tab'][0]['val']);
                array_push($sports_fav, $sport_count); 
            }
            echo "
            <script type='text/javascript' src='https://www.gstatic.com/charts/loader.js'></script>
            <script type='text/javascript'>
          google.charts.load('current', {'packages':['corechart']});
          google.charts.setOnLoadCallback(drawChart3);
          function drawChart3() {
            var data = google.visualization.arrayToDataTable([
              ['Cible', 'Sport les plus mis en Favoris'],"; 
              foreach($sports_fav as $var){
                echo '["'.$var["titre"].'",'.$var["nbr"].'],';
              }
              echo "]);";
            
    
            echo "var options = {
              title: 'Sport les plus mis en favoris :',
              is3D: true,
            };
    
            var chart = new google.visualization.PieChart(document.getElementById('piechart3'));
    
            chart.draw(data, options);
          }
          </script>
          ";     
        }
        
         
?>
<html>
  <head>


    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          ['Cible', 'Catégorie les plus recherchées'],
          ['Femme',     <? echo $countf;?>],
          ['Homme',      <? echo $countm;?>],
          ['Equipements',  <? echo $counte;?>],
          ['Mixte', <? echo $count_mixte;?>],
          ['Enfant garçon',    <? echo $count_eg;?>],
          ['Enfant fille',    <? echo $count_ef;?>],
          ['Enfant Mixte',    <? echo $count_em;?>]
        ]);

        var options = {
          title: 'Catégorie les plus recherchées :',
          is3D: true,
          
        };
        

        var chart = new google.visualization.PieChart(document.getElementById('piechart'));

        chart.draw(data, options);
      }
    </script>

  </head>
  <body>
    <div id="piechart" style="width: 900px; height: 500px;"></div>
    <div id="piechart2" style="width: 900px; height: 500px;"></div>
    <div id="piechart3" style="width: 900px; height: 500px;"></div>
  </body>

  <style>
    /* Styles CSS pour le forum */
    body {
      font-family: Arial, sans-serif;
      background-color: #f7f7f7;
      margin: 0;
      padding: 20px;
    }
    
    .forum-post {
      background-color: #fff;
      border-radius: 5px;
      padding: 20px;
      margin-bottom: 20px;
      border: 1px solid rgb(0, 196, 65);
    }
    
    .post-header {
      display: flex;
      align-items: center;
      margin-bottom: 10px;
    }
    
    .post-header img {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      margin-right: 10px;
    }
    
    .post-content {
      margin-bottom: 10px;
    }
    
    .reply-button {
      background-color: rgb(0, 196, 65);
      color: #fff;
      border: none;
      padding: 10px 20px;
      border-radius: 5px;
      cursor: pointer;
    }
    
    .reply-button:hover {
      background-color: #0069d9;
    }
    
    .create-post-form {
      background-color: #fff;
      border-radius: 5px;
      padding: 20px;
      border: 1px solid rgb(0, 196, 65);
    }
    
    .create-post-form textarea {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 5px;
      resize: vertical;
      margin-bottom: 10px;
      margin-left: -10px;
    }

    header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 20px;
      border-radius: 5px;
      padding: 20px;
      border: 1px solid rgb(0, 196, 65);
      background-color: #fff;
    }
    
    header h1 {
      font-size: 24px;
      margin: 0;
    }
    
    .settings-button {
      background-color: rgb(0, 196, 65);
      color: #fff;
      border: none;
      padding: 10px 20px;
      border-radius: 5px;
      cursor: pointer;
      text-decoration: none;
    }
    
    .settings-button:hover {
      background-color: #0069d9;
    }
    
    .create-post-form button {
      background-color: rgb(0, 196, 65);
      color: #fff;
      border: none;
      padding: 10px 20px;
      border-radius: 5px;
      cursor: pointer;
    }
    
    .create-post-form button:hover {
      background-color: #0069d9;
    }
  </style>
</html>

