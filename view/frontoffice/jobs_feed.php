<?php $pageTitle = "Browse Jobs"; $pageCSS = "feeds.css"; ?>

<?php
if (!isset($content)) {
    $content = __FILE__;
    include 'layout_front.php';
    exit();
}
?>
<!-- Included inside layout_front.php -->

<div class="page-header">
  <h1 class="page-header__title">
    <i data-lucide="search" style="width:28px;height:28px;color:var(--accent-primary);"></i>
    Browse Jobs &amp; Tasks
  </h1>
  <p class="page-header__subtitle">Trouvez l'offre qui correspond à votre profil</p>
</div>

<!-- ═══ FILTER BAR ═══ -->
<div class="job-filter-bar mb-6" id="job-filters">
  <div class="input-icon-wrapper search-input" style="flex:1;max-width:280px;">
    <i data-lucide="search" style="width:16px;height:16px;"></i>
    <input type="text" class="input" id="job-search" placeholder="Search by keyword...">
  </div>
  <div class="input-icon-wrapper" style="max-width:180px;">
    <i data-lucide="map-pin" style="width:16px;height:16px;"></i>
    <input type="text" class="input" id="job-location" placeholder="Location...">
  </div>
  <select class="select" id="job-type" style="max-width:160px;">
    <option value="">Job Offer</option>
    <option>Job Offer</option>
    <option>Internship</option>
    <option>Freelance</option>
  </select>
  <select class="select" id="job-time" style="max-width:140px;">
    <option value="">Full-time</option>
    <option>Full-time</option>
    <option>Part-time</option>
    <option>Contract</option>
  </select>
  <div class="mode-toggle" id="mode-toggle">
    <button class="mode-toggle__option active" data-mode="all">All</button>
    <button class="mode-toggle__option" data-mode="remote">Remote</button>
    <button class="mode-toggle__option" data-mode="onsite">On-site</button>
    <button class="mode-toggle__option" data-mode="hybrid">Hybrid</button>
  </div>
  <select class="select" id="job-sort" style="max-width:130px;">
    <option>Newest</option>
    <option>Oldest</option>
    <option>Salary ↑</option>
    <option>Salary ↓</option>
  </select>
  <button class="btn btn-primary" id="job-search-btn">
    <i data-lucide="search" style="width:16px;height:16px;"></i>
    Search
  </button>
</div>

<!-- Results Info -->
<div class="results-info mb-4">
  <strong>24</strong> results found
</div>

<!-- ═══ JOB CARDS GRID ═══ -->
<div class="job-cards-grid stagger">
  <?php
  $jobs = [
    ['title' => 'Senior Full Stack Developer', 'company' => 'TechSphere Inc.', 'logo' => 'TS', 'desc' => 'We are looking for an experienced Full Stack Developer to join our growing team and build innovative web applications.', 'location' => 'Tunis, Tunisia', 'type' => 'Full-time', 'mode' => 'Hybrid', 'salary' => '3,500 - 5,000 TND', 'date' => 'Il y a 2h', 'badge' => 'Job', 'badge_class' => 'badge-info'],
    ['title' => 'Data Engineer', 'company' => 'DataFlow Analytics', 'logo' => 'DF', 'desc' => 'Join our data team to build robust data pipelines and infrastructure supporting real-time analytics and ML models.', 'location' => 'Remote', 'type' => 'Full-time', 'mode' => 'Remote', 'salary' => '4,000 - 6,000 TND', 'date' => 'Il y a 5h', 'badge' => 'Job', 'badge_class' => 'badge-info'],
    ['title' => 'UI/UX Designer', 'company' => 'InnoLab Design', 'logo' => 'IL', 'desc' => 'Passionate about user-centered design? Join us to create stunning interfaces for our SaaS products.', 'location' => 'Sfax, Tunisia', 'type' => 'Part-time', 'mode' => 'On-site', 'salary' => '2,000 - 3,500 TND', 'date' => 'Il y a 1j', 'badge' => 'Job', 'badge_class' => 'badge-info'],
    ['title' => 'DevOps Engineer', 'company' => 'CloudPeak Systems', 'logo' => 'CP', 'desc' => 'Automate, monitor, and optimize our cloud infrastructure. Experience with AWS, Docker, and Kubernetes required.', 'location' => 'Tunis, Tunisia', 'type' => 'Full-time', 'mode' => 'Hybrid', 'salary' => '4,500 - 7,000 TND', 'date' => 'Il y a 1j', 'badge' => 'Job', 'badge_class' => 'badge-info'],
    ['title' => 'Marketing Digital Intern', 'company' => 'GrowthLab', 'logo' => 'GL', 'desc' => 'Stage de 6 mois en marketing digital : SEO, campagnes ads, analytics. Encadrement par une équipe senior.', 'location' => 'Sousse, Tunisia', 'type' => 'Internship', 'mode' => 'On-site', 'salary' => '800 - 1,200 TND', 'date' => 'Il y a 2j', 'badge' => 'Stage', 'badge_class' => 'badge-warning'],
    ['title' => 'Mobile Developer (React Native)', 'company' => 'AppForge', 'logo' => 'AF', 'desc' => 'Build cross-platform mobile apps with React Native. Strong understanding of mobile UX patterns required.', 'location' => 'Remote', 'type' => 'Freelance', 'mode' => 'Remote', 'salary' => '3,000 - 4,500 TND', 'date' => 'Il y a 3j', 'badge' => 'Freelance', 'badge_class' => 'badge-success'],
    ['title' => 'Cybersecurity Analyst', 'company' => 'SecureNet SA', 'logo' => 'SN', 'desc' => 'Monitor and respond to security threats. Conduct vulnerability assessments and implement security solutions.', 'location' => 'Tunis, Tunisia', 'type' => 'Full-time', 'mode' => 'On-site', 'salary' => '3,800 - 5,500 TND', 'date' => 'Il y a 3j', 'badge' => 'Job', 'badge_class' => 'badge-info'],
    ['title' => 'Product Manager', 'company' => 'TechSphere Inc.', 'logo' => 'TS', 'desc' => 'Drive product strategy and roadmap for our B2B SaaS platform. Experience in agile methodologies preferred.', 'location' => 'Tunis, Tunisia', 'type' => 'Full-time', 'mode' => 'Hybrid', 'salary' => '5,000 - 8,000 TND', 'date' => 'Il y a 4j', 'badge' => 'Job', 'badge_class' => 'badge-info'],
  ];
  foreach ($jobs as $i => $j):
  ?>
  <div class="job-card animate-on-scroll" id="job-card-<?php echo $i; ?>">
    <div class="job-card__header">
      <div class="job-card__company-logo"><?php echo $j['logo']; ?></div>
      <div class="job-card__title-group">
        <h3 class="job-card__title"><?php echo $j['title']; ?></h3>
        <span class="job-card__company"><?php echo $j['company']; ?></span>
      </div>
      <span class="badge <?php echo $j['badge_class']; ?> job-card__type-badge"><?php echo $j['badge']; ?></span>
    </div>
    <p class="job-card__description"><?php echo $j['desc']; ?></p>
    <div class="job-card__tags">
      <span class="job-card__tag"><i data-lucide="map-pin"></i> <?php echo $j['location']; ?></span>
      <span class="job-card__tag"><i data-lucide="clock"></i> <?php echo $j['type']; ?></span>
      <span class="job-card__tag"><i data-lucide="wifi"></i> <?php echo $j['mode']; ?></span>
      <span class="job-card__tag"><i data-lucide="banknote"></i> <?php echo $j['salary']; ?></span>
    </div>
    <div class="job-card__footer">
      <span class="job-card__date">
        <i data-lucide="calendar" style="width:12px;height:12px;"></i> <?php echo $j['date']; ?>
      </span>
      <button class="btn btn-sm btn-primary">
        <i data-lucide="send" style="width:14px;height:14px;"></i> Postuler
      </button>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Pagination -->
<div class="pagination">
  <button class="pagination__btn">&laquo;</button>
  <button class="pagination__btn active">1</button>
  <button class="pagination__btn">2</button>
  <button class="pagination__btn">3</button>
  <button class="pagination__btn">&raquo;</button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Mode toggle interaction
  document.querySelectorAll('.mode-toggle__option').forEach(function(btn) {
    btn.addEventListener('click', function() {
      document.querySelectorAll('.mode-toggle__option').forEach(function(b) { b.classList.remove('active'); });
      this.classList.add('active');
    });
  });
});
</script>
