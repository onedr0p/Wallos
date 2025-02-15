<?php
  require_once 'includes/header.php';
  require_once 'includes/getdbkeys.php';

  include_once 'includes/list_subscriptions.php';

  $sort = "next_payment";
  $sql = "SELECT * FROM subscriptions WHERE user_id = :userId ORDER BY next_payment ASC, inactive ASC";
  if (isset($_COOKIE['sortOrder']) && $_COOKIE['sortOrder'] != "") {
    $sort = $_COOKIE['sortOrder'];
    $allowedSortCriteria = ['name', 'id', 'next_payment', 'price', 'payer_user_id', 'category_id', 'payment_method_id'];
    $order = "ASC";
    if ($sort == "price" || $sort == "id") {
      $order = "DESC";
    }
    if (in_array($sort, $allowedSortCriteria)) {
      $sql = "SELECT * FROM subscriptions WHERE user_id = :userId ORDER BY $sort $order, inactive ASC";
    }
  }
        
  $stmt = $db->prepare($sql);
  $stmt->bindValue(':userId', $userId, SQLITE3_INTEGER);
  $result = $stmt->execute();
  if ($result) {
    $subscriptions = array();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
      $subscriptions[] = $row;
    }
  }

  $headerClass = count($subscriptions) > 0 ? "main-actions" : "main-actions hidden";
  $defaultLogo = $theme == "light" ? "images/siteicons/" . $colorTheme . "/wallos.png" : "images/siteicons/" . $colorTheme . "/walloswhite.png";
?>
    <style>
      .logo-preview:after {
        content: '<?= translate('upload_logo', $i18n) ?>';
      }
    </style>
    <section class="contain">
      <header class="<?= $headerClass ?>" id="main-actions">
        <button class="button" onClick="addSubscription()">
          <img class="button-icon" src="images/siteicons/plusicon.png">
          <?= translate('new_subscription', $i18n) ?>
        </button>
        <div class="top-actions">
          <div class="search">
            <input type="text" autocomplete="off" name="search" id="search" placeholder="<?= translate('search', $i18n) ?>" onkeyup="searchSubscriptions()" />
            <span class="fa-solid fa-magnifying-glass search-icon"></span>
          </div>  

          <div class="filtermenu on-dashboard">
            <button class="button" id="filtermenu-button" title="<?= translate("filter", $i18n) ?>">
              <i class="fa-solid fa-filter"></i>
            </button>
            <div class="filtermenu-content">
              <?php
                if (count($members) > 1) {
              ?>
                <div class="filtermenu-submenu">
                  <div class="filter-title" onClick="toggleSubMenu('member')"><?= translate("member", $i18n) ?></div>
                  <div class="filtermenu-submenu-content" id="filter-member">
                    <?php
                      foreach ($members as $member) {
                        $selectedClass = '';
                        if (isset($_GET['member']) && $_GET['member'] == $member['id']) {
                          $selectedClass = 'selected';
                        }
                        ?>
                          <div class="filter-item <?= $selectedClass ?>" data-memberid="<?= $member['id'] ?>"><?= $member['name'] ?></div>
                        <?php
                      }
                    ?>
                  </div>
                </div>
              <?php
                }
              ?>
              <?php
                if (count($categories) > 1) {
              ?>
                <div class="filtermenu-submenu">
                  <div class="filter-title" onClick="toggleSubMenu('category')"><?= translate("category", $i18n) ?></div>
                  <div class="filtermenu-submenu-content" id="filter-category">
                    <?php
                      foreach ($categories as $category) {
                        $selectedClass = '';
                        if (isset($_GET['category']) && $_GET['category'] == $category['id']) {
                          $selectedClass = 'selected';
                        }
                        ?>
                          <div class="filter-item <?= $selectedClass ?>" data-categoryid="<?= $category['id'] ?>"><?= $category['name'] ?></div>
                        <?php
                      }
                    ?>
                  </div>
                </div>
              <?php
                }
              ?>
              <?php
                if (count($payment_methods) > 1) {
              ?>
                <div class="filtermenu-submenu">
                  <div class="filter-title" onClick="toggleSubMenu('payment')"><?= translate("payment_method", $i18n) ?></div>
                  <div class="filtermenu-submenu-content" id="filter-payment">
                    <?php
                      foreach ($payment_methods as $payment) {
                        $selectedClass = '';
                        if (isset($_GET['payment']) && $_GET['payment'] == $payment['id']) {
                          $selectedClass = 'selected';
                        }
                        ?>
                          <div class="filter-item <?= $selectedClass ?>" data-paymentid="<?= $payment['id'] ?>"><?= $payment['name'] ?></div>
                        <?php
                      }
                    ?>
                  </div>
                </div>
              <?php
                }
              ?>
              <div class="filtermenu-submenu hide" id="clear-filters">
                <div class="filter-title filter-clear" onClick="clearFilters()">
                  <i class="fa-solid fa-times-circle"></i> <?= translate("clear", $i18n) ?>
                </div>
              </div>
            </div>
          </div>

          <div class="sort-container">
            <button class="button" value="Sort" onClick="toggleSortOptions()" id="sort-button" title="<?= translate('sort', $i18n) ?>">
              <img src="images/siteicons/sort.png" class="button-icon" />
            </button>
            <div class="sort-options" id="sort-options">
              <ul>
                <li <?= $sort == "name" ? 'class="selected"' : "" ?> onClick="setSortOption('name')" id="sort-name"><?= translate('name', $i18n) ?></li>
                <li <?= $sort == "id" ? 'class="selected"' : "" ?> onClick="setSortOption('id')" id="sort-id"><?= translate('last_added', $i18n) ?></li>
                <li <?= $sort == "price" ? 'class="selected"' : "" ?> onClick="setSortOption('price')" id="sort-price"><?= translate('price', $i18n) ?></li>
                <li <?= $sort == "next_payment" ? 'class="selected"' : "" ?> onClick="setSortOption('next_payment')" id="sort-next_payment"><?= translate('next_payment', $i18n) ?></li>
                <li <?= $sort == "payer_user_id" ? 'class="selected"' : "" ?> onClick="setSortOption('payer_user_id')" id="sort-payer_user_id"><?= translate('member', $i18n) ?></li>
                <li <?= $sort == "category_id" ? 'class="selected"' : "" ?> onClick="setSortOption('category_id')" id="sort-category_id"><?= translate('category', $i18n) ?></li>
                <li <?= $sort == "payment_method_id" ? 'class="selected"' : "" ?> onClick="setSortOption('payment_method_id')" id="sort-payment_method_id"><?= translate('payment_method', $i18n) ?></li>
              </ul>
            </div>
          </div>
        </div>
      </header>
      <div class="subscriptions" id="subscriptions">
        <?php
          foreach ($subscriptions as $subscription) {
            if ($subscription['inactive'] == 1 && isset($settings['hideDisabledSubscriptions']) && $settings['hideDisabledSubscriptions'] === 'true') {
              continue;
            }
            $id = $subscription['id'];
            $print[$id]['id'] = $id;
            $print[$id]['logo'] = $subscription['logo'] != "" ? "images/uploads/logos/".$subscription['logo'] : $defaultLogo;
            $print[$id]['name']= $subscription['name'];
            $cycle = $subscription['cycle'];
            $frequency = $subscription['frequency'];
            $print[$id]['billing_cycle'] = getBillingCycle($cycle, $frequency, $i18n);
            $paymentMethodId = $subscription['payment_method_id'];
            $print[$id]['currency_code'] = $currencies[$subscription['currency_id']]['code'];
            $currencyId = $subscription['currency_id'];
            $print[$id]['next_payment'] = date('M d, Y', strtotime($subscription['next_payment']));
            $paymentIconFolder = (strpos($payment_methods[$paymentMethodId]['icon'], 'images/uploads/icons/') !== false) ? "" : "images/uploads/logos/";
            $print[$id]['payment_method_icon'] = $paymentIconFolder . $payment_methods[$paymentMethodId]['icon'];
            $print[$id]['payment_method_name'] = $payment_methods[$paymentMethodId]['name'];
            $print[$id]['payment_method_id'] = $paymentMethodId;
            $print[$id]['category_id'] = $subscription['category_id'];
            $print[$id]['payer_user_id'] = $subscription['payer_user_id'];
            $print[$id]['price'] = floatval($subscription['price']);
            $print[$id]['inactive'] = $subscription['inactive'];
            $print[$id]['url'] = $subscription['url'];
            $print[$id]['notes'] = $subscription['notes'];

            if (isset($settings['convertCurrency']) && $settings['convertCurrency'] === 'true' && $currencyId != $mainCurrencyId) {
              $print[$id]['price'] = getPriceConverted($print[$id]['price'], $currencyId, $db);
              $print[$id]['currency_code'] = $currencies[$mainCurrencyId]['code'];
            }
            if (isset($settings['showMonthlyPrice']) && $settings['showMonthlyPrice'] === 'true') {
              $print[$id]['price'] = getPricePerMonth($cycle, $frequency, $print[$id]['price']);
            }
          }

          if (isset($print)) {
            printSubscriptions($print, $sort, $categories, $members, $i18n, $colorTheme);
          }
          $db->close();

          if (count($subscriptions) == 0) {
            ?>
              <div class="empty-page">
                <img src="images/siteimages/empty.png" alt="<?= translate('empty_page', $i18n) ?>" />
                <p>
                  <?= translate('no_subscriptions_yet', $i18n) ?>
                </p>
                <button class="button" onClick="addSubscription()">
                  <img class="button-icon" src="images/siteicons/plusicon.png">
                  <?= translate('add_first_subscription', $i18n) ?>
                </button>
              </div>
            <?php
          }
        ?>
      </div>

      <section class="subscription-form" id="subscription-form">
        <header>
          <h3 id="form-title"><?= translate('add_subscription', $i18n) ?></h3>
          <span class="fa-solid fa-xmark close-form" onClick="closeAddSubscription()"></span>
        </header>
        <form action="endpoints/subscription/add.php" method="post" id="subs-form">
          
          <div class="form-group-inline">
          <input type="text" id="name" name="name" placeholder="<?= translate('subscription_name', $i18n) ?>" onchange="setSearchButtonStatus()" onkeypress="this.onchange();" onpaste="this.onchange();" oninput="this.onchange();" required>
            <label for="logo" class="logo-preview">
              <img src="" alt="<?= translate('logo_preview', $i18n) ?>" id="form-logo"> 
            </label>
            <input type="file" id="logo" name="logo" accept="image/jpeg, image/png, image/gif, image/webp" onchange="handleFileSelect(event)" class="hidden-input">
            <input type="hidden" id="logo-url" name="logo-url">
            <div id="logo-search-button" class="image-button medium disabled" title="<?= translate('search_logo', $i18n) ?>" onClick="searchLogo()">
              <img src="images/siteicons/<?= $colorTheme ?>/websearch.png">
            </div>
            <input type="hidden" id="id" name="id">
            <div id="logo-search-results" class="logo-search">
              <header>
                <?= translate('web_search', $i18n) ?>
                <span class="fa-solid fa-xmark close-logo-search" onClick="closeLogoSearch()"></span>
              </header>
              <div id="logo-search-images"></div>
            </div>
          </div>

          <div class="form-group-inline">
            <input type="number" step="0.01" id="price" name="price" placeholder="<?= translate('price', $i18n) ?>" required>
            <select id="currency" name="currency_id" placeholder="<?= translate('add_subscription', $i18n) ?>">
              <?php
                foreach ($currencies as $currency) {
                  $selected = ($currency['id'] == $main_currency) ? 'selected' : '';
              ?>
                  <option value="<?= $currency['id'] ?>" <?= $selected ?>><?= $currency['name'] ?></option>
              <?php
                }
              ?>
            </select>
          </div>
          
          <div class="form-group">
            <div class="inline">
              <div class="split66">
                <label for="cycle"><?= translate('payment_every', $i18n) ?></label>
                <div class="inline">
                  <select id="frequency" name="frequency" placeholder="<?= translate('frequency', $i18n) ?>">
                  <?php
                      foreach ($frequencies as $frequency) {
                    ?>
                      <option value="<?= $frequency['id'] ?>"><?= $frequency['name'] ?></option>
                    <?php
                      }
                    ?>
                  </select>
                  <select id="cycle" name="cycle" placeholder="Cycle">
                  <?php
                      foreach ($cycles as $cycle) {
                    ?>
                      <option value="<?= $cycle['id'] ?>" <?= $cycle['id'] == 3 ? "selected" : "" ?>><?= translate(strtolower($cycle['name']), $i18n) ?></option>
                    <?php
                      }
                    ?>
                  </select>
                </div>
              </div>
              <div class="split33">
                <label for="next_payment"><?= translate('next_payment', $i18n) ?></label>
                <input type="date" id="next_payment" name="next_payment" required>
              </div>
            </div>
          </div>

          <div class="form-group-inline">
            <input type="checkbox" id="notifications" name="notifications" onchange="toggleNotificationDays()">
            <label for="notifications"><?= translate('enable_notifications', $i18n) ?></label>
          </div>

          <div class="form-group">
            <label for="notify_days_before"><?= translate('notify_me', $i18n) ?></label>
            <select id="notify_days_before" name="notify_days_before" disabled>
              <option value="0"><?= translate('default_value_from_settings', $i18n) ?></option>
              <option value="1">1 <?= translate('day_before', $i18n) ?></option>
              <?php
                for ($i = 2; $i <= 90; $i++) {
              ?>
                <option value="<?= $i ?>"><?= $i ?> <?= translate('days_before', $i18n) ?></option>
              <?php
                }
              ?>
              </select>
          </div>

          <div class="form-group">
            <label for="payment_method"><?= translate('payment_method', $i18n) ?></label>
            <select id="payment_method" name="payment_method_id">
              <?php
                foreach ($payment_methods as $payment) {
              ?>
                <option value="<?= $payment['id'] ?>">
                  <?= $payment['name'] ?>
                </option>
              <?php
                }
              ?>
            </select>
          </div>

          <div class="form-group">
            <label for="category"><?= translate('category', $i18n) ?></label>
            <select id="category" name="category_id">
              <?php
                foreach ($categories as $category) {
              ?>
                <option value="<?= $category['id'] ?>">
                  <?= $category['name'] ?>
                </option>
              <?php
                }
              ?>
            </select>
          </div>
          

          <div class="form-group">
            <label for="payer_user"><?= translate('paid_by', $i18n) ?></label>
            <select id="payer_user" name="payer_user_id">
              <?php
                foreach ($members as $member) {
              ?>
                <option value="<?= $member['id'] ?>"><?= $member['name'] ?></option>
              <?php
                }
              ?>
            </select>
          </div>

          <div class="form-group">
            <input type="text" id="url" name="url" placeholder="<?= translate('url', $i18n) ?>">
          </div>

          <div class="form-group">
            <input type="text" id="notes" name="notes" placeholder="<?= translate('notes', $i18n) ?>">
          </div>

          <div class="form-group-inline">
            <input type="checkbox" id="inactive" name="inactive">
            <label for="inactive"><?= translate('inactive', $i18n) ?></label>
          </div>

          <div class="buttons">
            <input type="button" value="<?= translate('delete', $i18n) ?>" class="warning-button left thin" id="deletesub" style="display: none">
            <input type="button" value="<?= translate('cancel', $i18n) ?>" class="secondary-button thin" onClick="closeAddSubscription()">
            <input type="submit" value="<?= translate('save', $i18n) ?>" class="thin" id="save-button">
          </div>
        </form>
      </section>
    </section>
    <script src="scripts/dashboard.js?<?= $version ?>"></script>

<?php
  require_once 'includes/footer.php';
?>
