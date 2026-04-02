<header>
      <div class="container">
          <div class="navbar">
              <nav>
                  <ul>
                      <li><a href="index.php">Home</a></li>
                      <!--categories -->
                      <li class="dropdown-cat"><a href="categories.php">Categories</a><ul class="dropdown-menu-cat">
  <li><a href="dolls.php"><img src="assets/images/doll.png" width="25ex" style="margin-right:2ex;">Dolls</a></li>
  <li><a href="legoSet.php"><img src="assets/images/Lego.png" width="25ex" style="margin-right:2ex;">Lego Sets</a></li>
  <li><a href="remoteControlToys.php"><img src="assets/images/remotecontrol.png" width="25ex" style="margin-right:2ex;">Remote Control Toys</a></li>
  <li><a href="electronics.php"><img src="assets/images/KidsElectronic.png" width="25ex" style="margin-right:2ex;">Kids electronics</a></li>
  <li><a href="sportsEquipment.php"><img src="assets/images/sport.png" width="25ex" style="margin-right:2ex;">Sport equipment</a></li>
  <li><a href="brainTeaser.php"><img src="assets/images/brainTeaser.png" width="25ex" style="margin-right:2ex;">Brain Teaser</a></li>
  <li><a href="trainSets.php"><img src="assets/images/train.png" width="25ex" style="margin-right:2ex;">Train Sets</a></li>
  <li><a href="plushToys.php"><img src="assets/images/plush-toy.png" width="25ex" style="margin-right:2ex;">Plush Toys</a></li>
  <li><a href="outdoorToys.php"><img src="assets/images/outdoors.png" width="25ex" style="margin-right:2ex;">Outdoor Toys</a></li>
           
</ul>
</li>

                      <li><a href="about.html">About</a></li>
                      <li><a href="contactus.html">Contact Us</a></li>
                  </ul>
              </nav>
              <div class="logo">
                  <img src="assets/images/HOOPPA_label" alt="Logo"> 
              </div> 
              <nav style="position: relative; left: 70px;">
                  <ul> 
                     <li style="position: relative;">
              <a href="javascript:void(0);" id="cart-icon">
                <img src="assets/images/cartIcon.png" alt="Cart" width="30px" height="30px" />
                <?php
                  $cartCount = 0;
                  if (!empty($_SESSION['cart'])) {
                      foreach ($_SESSION['cart'] as $item) {
                          $cartCount += $item['quantity'];
                      }
                  }
                  if ($cartCount > 0) {
                      echo "<span class='cart-badge'>{$cartCount}</span>";
                  }
                ?>
              </a>
            </li>

                      <li><a href="LoginPage.html"><div>log in</div></a></li>
                  </ul>
              </nav>
          </div>
      </div>
  </header>