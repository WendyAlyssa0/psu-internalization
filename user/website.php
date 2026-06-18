<?php

$programs = [

[
    'id' => 1,
    'emoji' => '🇯🇵',
    'title' => 'JENESYS Student Exchange Program',
    'institution' => 'Japan International Cooperation Center (JICE)',
    'country' => 'Japan',
    'type' => 'exchange',
    'duration' => '2 weeks',
    'deadline' => 'Jul 15, 2025',
    'urgent' => true,
    'funding' => 'Full Scholarship',
    'featured' => true,
    'slots' => 5,
    'desc' => 'A two-week cultural and academic exchange program in Japan.',
],

[
    'id' => 2,
    'emoji' => '🇰🇷',
    'title' => 'Korea-ASEAN IT Internship',
    'institution' => 'Seoul National University of Science & Technology',
    'country' => 'South Korea',
    'type' => 'internship',
    'duration' => '4 months',
    'deadline' => 'Aug 1, 2025',
    'urgent' => false,
    'funding' => 'Partial Scholarship',
    'featured' => true,
    'slots' => 3,
    'desc' => 'Hands-on IT internship embedded within university research labs in Seoul.',
],

];


$search  = $_GET['search'] ?? '';
$type    = $_GET['type'] ?? '';
$country = $_GET['country'] ?? '';

$filteredPrograms = array_filter($programs, function($p) use ($search,$type,$country){

    $matchSearch =
        empty($search) ||
        stripos($p['title'],$search) !== false ||
        stripos($p['country'],$search) !== false ||
        stripos($p['institution'],$search) !== false;

    $matchType =
        empty($type) ||
        $p['type'] === $type;

    $matchCountry =
        empty($country) ||
        $p['country'] === $country;

    return $matchSearch && $matchType && $matchCountry;
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>PSU Internalization — Student Programs Portal</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="../asset/css/website.css">

</head>
<body>

<!-- NAV -->
<nav>
  <div class="nav-logo">PSU <span>Internalization</span></div>
  <ul class="nav-links">
    <li><a href="#">Browse Programs</a></li>
    <li><a href="#">Partner Institutions</a></li>
    <li><a href="#">How It Works</a></li>
    <li><a href="#" class="nav-cta">Apply Now</a></li>
  </ul>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-inner">
    <div class="hero-eyebrow">Student Mobility &amp; Internship Portal</div>
    <h1>Explore programs <em>beyond</em> the campus.</h1>
    <p>Discover internships, exchange programs, and research opportunities from partner institutions around the world — open to PSU students.</p>
    <div class="search-bar">
      <input type="text" placeholder="Search by program name, country, or field…" id="searchInput"/>
      <select id="typeFilter">
        <option value="">All Types</option>
        <option value="exchange">Exchange</option>
        <option value="internship">Internship</option>
        <option value="research">Research</option>
        <option value="training">Training</option>
      </select>
      <button onclick="applySearch()">Search</button>
    </div>
    <div class="stats-strip">
      <div class="stat"><strong>42</strong> Active Programs</div>
      <div class="stat"><strong>41</strong> Partner Countries</div>
      <div class="stat"><strong>130+</strong> Students Placed</div>
    </div>
  </div>
</section>

<!-- MAIN -->
<div class="page-body">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="filter-card">
      <h3>Program Type</h3>
      <div class="filter-group">
        <label><input type="checkbox" data-filter="type" value="exchange" onchange="filterCards()"> Student Exchange <span class="badge-count">14</span></label>
        <label><input type="checkbox" data-filter="type" value="internship" onchange="filterCards()"> Internship <span class="badge-count">18</span></label>
        <label><input type="checkbox" data-filter="type" value="research" onchange="filterCards()"> Research <span class="badge-count">6</span></label>
        <label><input type="checkbox" data-filter="type" value="training" onchange="filterCards()"> Training <span class="badge-count">4</span></label>
      </div>
    </div>

    <div class="filter-card">
      <h3>Country / Region</h3>
      <select id="countryFilter" onchange="filterCards()">
        <option value="">All Countries</option>
        <option>Japan</option>
        <option>South Korea</option>
        <option>Germany</option>
        <option>Australia</option>
        <option>Malaysia</option>
        <option>Singapore</option>
        <option>United States</option>
        <option>China</option>
        <option>France</option>
        <option>New Zealand</option>
        <option>Thailand</option>
        <option>India</option>
        <option>Taiwan</option>
        <option>Vietnam</option>
        <option>Indonesia</option>
        <option>Switzerland</option>
        <option>Brazil</option>
        <option>United Kingdom</option>
        <option>Russia</option>
        <option>Canada</option>
        <option>Portugal</option>
        <option>Mexico</option>
        <option>South Africa</option>
        <option>UAE</option>
        <option>Netherlands</option>
        <option>Chile</option>
        <option>Sweden</option>
        <option>Italy</option>
        <option>Finland</option>
        <option>Poland</option>
        <option>Austria</option>
        <option>Norway</option>
        <option>Greece</option>
        <option>Israel</option>
        <option>Hungary</option>
        <option>Peru</option>
        <option>Belgium</option>
        <option>Romania</option>
        <option>Czech Republic</option>
        <option>Ireland</option>
        <option>Denmark</option>
        <option>Philippines</option>
      </select>
    </div>

    <div class="filter-card">
      <h3>Duration</h3>
      <div class="filter-group">
        <label><input type="checkbox" onchange="filterCards()"> 1 – 3 months</label>
        <label><input type="checkbox" onchange="filterCards()"> 4 – 6 months</label>
        <label><input type="checkbox" onchange="filterCards()"> 1 semester</label>
        <label><input type="checkbox" onchange="filterCards()"> 1 academic year</label>
      </div>
    </div>

    <div class="filter-card">
      <h3>Funding</h3>
      <div class="filter-group">
        <label><input type="checkbox" data-filter="funding" value="full scholarship" onchange="filterCards()"> With Scholarship</label>
        <label><input type="checkbox" data-filter="funding" value="partial" onchange="filterCards()"> Partially Funded</label>
        <label><input type="checkbox" data-filter="funding" value="self-funded" onchange="filterCards()"> Self-funded</label>
      </div>
    </div>

    <button class="clear-btn" onclick="clearFilters()">Clear all filters</button>
  </aside>

  <!-- LISTINGS -->
  <main>
    <div class="listings-header">
      <div>
        <h2>Available Programs</h2>
        <span id="resultCount">Showing 6 of 42 programs</span>
      </div>
      <select class="sort-select" id="sortSelect">
        <option value="deadline">Sort: Deadline (Soonest)</option>
        <option value="slots">Sort: Most Slots</option>
        <option value="az">Sort: A → Z</option>
      </select>
    </div>

    <div id="programList">

<?php foreach($programs as $p): ?>

<div class="program-card <?= $p['featured'] ? 'featured' : '' ?>">

    <div class="card-logo">
        <?= $p['emoji'] ?>
    </div>

    <div class="card-body">

        <div class="card-top">

            <div>
                <div class="card-title">
                    <?= htmlspecialchars($p['title']) ?>
                </div>

                <div class="card-institution">
                    <?= htmlspecialchars($p['institution']) ?>
                    ·
                    <?= htmlspecialchars($p['country']) ?>
                </div>
            </div>

            <div style="text-align:right;">

                <?php if($p['featured']): ?>
                    <span class="featured-badge">
                        FEATURED
                    </span>
                <?php endif; ?>

                <div class="card-deadline <?= $p['urgent'] ? 'urgent' : '' ?>">
                    Deadline: <?= $p['deadline'] ?>
                </div>

            </div>

        </div>

        <div class="card-desc">
            <?= substr($p['desc'],0,120) ?>...
        </div>

        <div class="card-tags">
            <span class="tag type-<?= $p['type'] ?>">
                <?= ucfirst($p['type']) ?>
            </span>

            <span class="tag">
                ⏱ <?= $p['duration'] ?>
            </span>

            <span class="tag">
                🎓 <?= $p['funding'] ?>
            </span>

            <span class="tag">
                👥 <?= $p['slots'] ?> Slots
            </span>
        </div>

    </div>

</div>

<?php endforeach; ?>

</div>

    <div class="pagination" id="paginationBar"></div>
  </main>
</div>

<!-- MODAL -->
<div class="modal-overlay" id="modalOverlay" onclick="closeModal(event)">
  <div class="modal" id="modalBox">
    <div class="modal-header">
      <div class="modal-logo" id="modalLogo"></div>
      <div>
        <div class="modal-title" id="modalTitle"></div>
        <div class="modal-institution" id="modalInstitution"></div>
        <div class="card-tags" id="modalTags"></div>
      </div>
      <button class="modal-close" onclick="closeModalDirect()">✕</button>
    </div>
    <div class="modal-body">
      <div class="modal-section">
        <h4>About this Program</h4>
        <p id="modalDesc"></p>
      </div>
      <div class="modal-section">
        <h4>Program Details</h4>
        <div class="modal-meta" id="modalMeta"></div>
      </div>
      <div class="modal-section">
        <h4>Requirements</h4>
        <ul class="requirements-list" id="modalReqs"></ul>
      </div>
      <button class="apply-btn">Apply for this Program</button>
    </div>
  </div>
</div>

<!-- FOOTER -->
<footer>
  <strong>PSU Internalization Management System</strong><br/>
  Pangasinan State University · Office of International Affairs · <a href="#" style="color:rgba(255,255,255,.55)">Contact Us</a>
</footer>

<script>

  const PER_PAGE = 8;
  let currentPage = 1;
  let filteredPrograms = [...programs];

  function renderPrograms(list, page) {
    currentPage = page || 1;
    filteredPrograms = list;
    const total = list.length;
    const totalPages = Math.ceil(total / PER_PAGE);
    const start = (currentPage - 1) * PER_PAGE;
    const pageItems = list.slice(start, start + PER_PAGE);

    const container = document.getElementById('programList');
    container.innerHTML = '';

    if (total === 0) {
      container.innerHTML = '<div style="text-align:center;padding:60px 0;color:var(--gray500);">No programs match your filters. Try adjusting your search.</div>';
      renderPagination(0, 1);
      document.getElementById('resultCount').textContent = 'No programs found';
      return;
    }

    pageItems.forEach(p => {
      const card = document.createElement('div');
      card.className = 'program-card' + (p.featured ? ' featured' : '');
      card.onclick = () => openModal(p);
      card.innerHTML = `
        <div class="card-logo">${p.emoji}</div>
        <div class="card-body">
          <div class="card-top">
            <div>
              <div class="card-title">${p.title}</div>
              <div class="card-institution">${p.institution} · ${p.country}</div>
            </div>
            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:6px;flex-shrink:0;">
              ${p.featured ? '<span class="featured-badge">FEATURED</span>' : ''}
              <span class="card-deadline${p.urgent ? ' urgent' : ''}">Deadline: ${p.deadline}</span>
            </div>
          </div>
          <div class="card-desc">${p.desc.substring(0,120)}…</div>
          <div class="card-tags">
            <span class="tag type-${p.type}">${capitalize(p.type)}</span>
            <span class="tag">⏱ ${p.duration}</span>
            <span class="tag">🎓 ${p.funding}</span>
            <span class="tag">👥 ${p.slots} slot${p.slots > 1 ? 's' : ''} available</span>
          </div>
        </div>`;
      container.appendChild(card);
    });

    document.getElementById('resultCount').textContent =
      `Showing ${start + 1}–${Math.min(start + PER_PAGE, total)} of ${total} programs`;

    renderPagination(totalPages, currentPage);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  function renderPagination(totalPages, active) {
    const pg = document.getElementById('paginationBar');
    pg.innerHTML = '';
    if (totalPages <= 1) return;

    const btn = (label, page, disabled, isActive) => {
      const b = document.createElement('button');
      b.className = 'page-btn' + (isActive ? ' active' : '');
      b.textContent = label;
      b.disabled = disabled;
      if (!disabled && !isActive) b.onclick = () => renderPrograms(filteredPrograms, page);
      return b;
    };

    pg.appendChild(btn('‹', active - 1, active === 1, false));

    let pages = [];
    if (totalPages <= 7) {
      pages = Array.from({length: totalPages}, (_, i) => i + 1);
    } else {
      pages = [1];
      if (active > 3) pages.push('…');
      for (let i = Math.max(2, active - 1); i <= Math.min(totalPages - 1, active + 1); i++) pages.push(i);
      if (active < totalPages - 2) pages.push('…');
      pages.push(totalPages);
    }

    pages.forEach(p => {
      if (p === '…') {
        const s = document.createElement('span');
        s.textContent = '…';
        s.style.cssText = 'padding:0 6px;color:var(--gray500);line-height:36px;';
        pg.appendChild(s);
      } else {
        pg.appendChild(btn(p, p, false, p === active));
      }
    });

    pg.appendChild(btn('›', active + 1, active === totalPages, false));
  }

  function openModal(p) {
    document.getElementById('modalLogo').textContent = p.emoji;
    document.getElementById('modalTitle').textContent = p.title;
    document.getElementById('modalInstitution').textContent = p.institution + ' · ' + p.country;
    document.getElementById('modalTags').innerHTML = `
      <span class="tag type-${p.type}">${capitalize(p.type)}</span>
      <span class="tag">🎓 ${p.funding}</span>`;
    document.getElementById('modalDesc').textContent = p.desc;
    document.getElementById('modalMeta').innerHTML = `
      <div class="meta-item"><div class="label">Duration</div><div class="value">${p.duration}</div></div>
      <div class="meta-item"><div class="label">Slots Available</div><div class="value">${p.slots} student${p.slots>1?'s':''}</div></div>
      <div class="meta-item"><div class="label">Application Deadline</div><div class="value">${p.deadline}</div></div>
      <div class="meta-item"><div class="label">Funding</div><div class="value">${p.funding}</div></div>`;
    document.getElementById('modalReqs').innerHTML = p.requirements.map(r => `<li>${r}</li>`).join('');
    document.getElementById('modalOverlay').classList.add('open');
  }

  function closeModal(e) {
    if (e.target === document.getElementById('modalOverlay')) closeModalDirect();
  }
  function closeModalDirect() {
    document.getElementById('modalOverlay').classList.remove('open');
  }

  function applySearch() {
    const q = document.getElementById('searchInput').value.toLowerCase();
    const type = document.getElementById('typeFilter').value;
    const checkedTypes = [...document.querySelectorAll('.filter-group input[type=checkbox][data-filter=type]:checked')].map(c => c.value);
    const checkedFunding = [...document.querySelectorAll('.filter-group input[type=checkbox][data-filter=funding]:checked')].map(c => c.value);
    const countryVal = document.getElementById('countryFilter').value;

    const filtered = programs.filter(p => {
      const matchQ = !q || p.title.toLowerCase().includes(q) || p.country.toLowerCase().includes(q) || p.institution.toLowerCase().includes(q);
      const matchType = (!type || p.type === type) && (checkedTypes.length === 0 || checkedTypes.includes(p.type));
      const matchCountry = !countryVal || p.country === countryVal;
      const matchFunding = checkedFunding.length === 0 || checkedFunding.some(f => p.funding.toLowerCase().includes(f));
      return matchQ && matchType && matchCountry && matchFunding;
    });
    renderPrograms(filtered, 1);
  }

  function filterCards() { applySearch(); }

  function clearFilters() {
    document.querySelectorAll('.filter-group input[type=checkbox]').forEach(cb => cb.checked = false);
    document.getElementById('searchInput').value = '';
    document.getElementById('typeFilter').value = '';
    document.getElementById('countryFilter').value = '';
    renderPrograms(programs, 1);
  }

  function capitalize(s) { return s.charAt(0).toUpperCase() + s.slice(1); }

  document.getElementById('searchInput').addEventListener('keydown', e => {
    if (e.key === 'Enter') applySearch();
  });

  // Sort
  document.getElementById('sortSelect').addEventListener('change', function() {
    const val = this.value;
    const sorted = [...filteredPrograms];
    if (val === 'deadline') sorted.sort((a, b) => new Date(a.deadline) - new Date(b.deadline));
    else if (val === 'slots') sorted.sort((a, b) => b.slots - a.slots);
    else if (val === 'az') sorted.sort((a, b) => a.title.localeCompare(b.title));
    renderPrograms(sorted, 1);
  });

  renderPrograms(programs, 1);
</script>
</body>
</html>