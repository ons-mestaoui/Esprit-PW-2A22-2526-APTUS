<?php $pageTitle = "Utilisateurs"; ?>

<?php
if (!isset($content)) {
    $content = __FILE__;
    include 'layout_back.php';
    exit();
}
?>
<!-- Included inside layout_back.php -->

<div class="back-page-header">
  <div class="back-page-header__row">
    <div>
      <h1>Utilisateurs</h1>
      <p>Gérez les comptes candidats, entreprises et administrateurs</p>
    </div>
    <div class="flex gap-3">
      <button class="btn btn-secondary" id="export-users-btn">
        <i data-lucide="download" style="width:18px;height:18px;"></i>
        Exporter
      </button>
      <button class="btn btn-primary" id="add-user-btn">
        <i data-lucide="user-plus" style="width:18px;height:18px;"></i>
        Ajouter
      </button>
    </div>
  </div>
</div>

<!-- ═══ Stats ═══ -->
<div class="grid grid-4 gap-6 mb-8 stagger">
  <div class="stat-card animate-on-scroll">
    <div>
      <div class="stat-card__label">Total Utilisateurs</div>
      <div class="stat-card__value">13,307</div>
    </div>
    <div class="stat-card__icon purple"><i data-lucide="users" style="width:22px;height:22px;"></i></div>
  </div>
  <div class="stat-card animate-on-scroll">
    <div>
      <div class="stat-card__label">Candidats</div>
      <div class="stat-card__value">12,450</div>
    </div>
    <div class="stat-card__icon blue"><i data-lucide="user" style="width:22px;height:22px;"></i></div>
  </div>
  <div class="stat-card animate-on-scroll">
    <div>
      <div class="stat-card__label">Entreprises</div>
      <div class="stat-card__value">845</div>
    </div>
    <div class="stat-card__icon teal"><i data-lucide="building-2" style="width:22px;height:22px;"></i></div>
  </div>
  <div class="stat-card animate-on-scroll">
    <div>
      <div class="stat-card__label">Admins</div>
      <div class="stat-card__value">12</div>
    </div>
    <div class="stat-card__icon orange"><i data-lucide="shield" style="width:22px;height:22px;"></i></div>
  </div>
</div>

<!-- ═══ Filter & Search ═══ -->
<div class="filter-bar mb-6">
  <div class="search-bar" style="flex:1;max-width:350px;">
    <i data-lucide="search" style="width:16px;height:16px;"></i>
    <input type="text" class="input" placeholder="Rechercher par nom, email..." id="user-search">
  </div>
  <select class="select" style="max-width:160px;" id="user-role-filter">
    <option value="">Tous les rôles</option>
    <option>Candidat</option>
    <option>Entreprise</option>
    <option>Admin</option>
  </select>
  <select class="select" style="max-width:140px;" id="user-status-filter">
    <option value="">Tous statuts</option>
    <option>Actif</option>
    <option>Inactif</option>
    <option>Banni</option>
  </select>
  <select class="select" style="max-width:140px;" id="user-sort">
    <option>Plus récent</option>
    <option>Plus ancien</option>
    <option>Nom (A-Z)</option>
  </select>
</div>

<!-- ═══ Users Table ═══ -->
<div class="card-flat" style="overflow:hidden;">
  <table class="data-table">
    <thead>
      <tr>
        <th>Utilisateur</th>
        <th>Email</th>
        <th>Rôle</th>
        <th>Statut</th>
        <th>Inscrit le</th>
        <th>Dernière activité</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $users = [
        ['name' => 'Amine Belloumi', 'email' => 'amine@email.com', 'role' => 'Candidat', 'status' => 'Actif', 'joined' => '08 Avr.', 'last' => 'Aujourd\'hui', 'initials' => 'AB', 'badge' => 'badge-info', 'status_badge' => 'badge-success'],
        ['name' => 'TechSphere Inc.', 'email' => 'contact@techsphere.com', 'role' => 'Entreprise', 'status' => 'Actif', 'joined' => '05 Avr.', 'last' => 'Hier', 'initials' => 'TS', 'badge' => 'badge-primary', 'status_badge' => 'badge-success'],
        ['name' => 'Sara Khediri', 'email' => 'sara.k@email.com', 'role' => 'Candidat', 'status' => 'Actif', 'joined' => '01 Avr.', 'last' => 'Il y a 2j', 'initials' => 'SK', 'badge' => 'badge-info', 'status_badge' => 'badge-success'],
        ['name' => 'DataFlow Analytics', 'email' => 'hr@dataflow.tn', 'role' => 'Entreprise', 'status' => 'Actif', 'joined' => '28 Mar.', 'last' => 'Il y a 3j', 'initials' => 'DF', 'badge' => 'badge-primary', 'status_badge' => 'badge-success'],
        ['name' => 'Mohamed Dridi', 'email' => 'med.dridi@email.com', 'role' => 'Candidat', 'status' => 'Inactif', 'joined' => '20 Mar.', 'last' => 'Il y a 15j', 'initials' => 'MD', 'badge' => 'badge-info', 'status_badge' => 'badge-warning'],
        ['name' => 'Admin User', 'email' => 'admin@aptus.com', 'role' => 'Admin', 'status' => 'Actif', 'joined' => '01 Jan.', 'last' => 'Aujourd\'hui', 'initials' => 'AD', 'badge' => 'badge-danger', 'status_badge' => 'badge-success'],
        ['name' => 'Fatma Jelassi', 'email' => 'fatma.j@email.com', 'role' => 'Candidat', 'status' => 'Banni', 'joined' => '15 Fév.', 'last' => 'Il y a 30j', 'initials' => 'FJ', 'badge' => 'badge-info', 'status_badge' => 'badge-danger'],
      ];
      foreach ($users as $u):
      ?>
      <tr>
        <td>
          <div class="flex items-center gap-3">
            <div class="avatar avatar-sm avatar-initials" style="width:32px;height:32px;font-size:11px;"><?php echo $u['initials']; ?></div>
            <span class="fw-medium"><?php echo $u['name']; ?></span>
          </div>
        </td>
        <td class="text-sm text-secondary"><?php echo $u['email']; ?></td>
        <td><span class="badge <?php echo $u['badge']; ?>"><?php echo $u['role']; ?></span></td>
        <td><span class="badge <?php echo $u['status_badge']; ?>"><?php echo $u['status']; ?></span></td>
        <td class="text-sm text-secondary"><?php echo $u['joined']; ?></td>
        <td class="text-sm text-secondary"><?php echo $u['last']; ?></td>
        <td>
          <div class="flex gap-1">
            <button class="btn btn-sm btn-ghost" title="Voir"><i data-lucide="eye" style="width:14px;height:14px;"></i></button>
            <button class="btn btn-sm btn-ghost" title="Éditer"><i data-lucide="pencil" style="width:14px;height:14px;"></i></button>
            <button class="btn btn-sm btn-ghost" style="color:var(--accent-tertiary);" title="Supprimer"><i data-lucide="trash-2" style="width:14px;height:14px;"></i></button>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <div class="pagination" style="padding:var(--space-4);">
    <button class="pagination__btn">&laquo;</button>
    <button class="pagination__btn active">1</button>
    <button class="pagination__btn">2</button>
    <button class="pagination__btn">3</button>
    <button class="pagination__btn">...</button>
    <button class="pagination__btn">45</button>
    <button class="pagination__btn">&raquo;</button>
  </div>
</div>
