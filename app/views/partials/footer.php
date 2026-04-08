<?php if (!empty($withSidebarLayout)): ?>
	</main>
</div>
<?php endif; ?>

<?php
$assetBase = rtrim($appUrl ?? '', '/');
if ($assetBase === '') {
	$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
	$scriptDir = rtrim($scriptDir, '/');
	$assetBase = ($scriptDir === '.' || $scriptDir === '') ? '' : $scriptDir;
}
?>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script src="<?php echo $assetBase; ?>/public/js/app.js"></script>
<script>
(function() {
	const stored = localStorage.getItem('theme-mode');
	if (stored === 'dark') {
		document.documentElement.setAttribute('data-theme', 'dark');
	}

	const closeSidebar = function() {
		document.body.classList.remove('sidebar-open');
	};

	const sidebarToggle = document.getElementById('sidebarToggle');
	const sidebarOverlay = document.getElementById('sidebarOverlay');
	if (sidebarToggle) {
		sidebarToggle.addEventListener('click', function() {
			document.body.classList.toggle('sidebar-open');
		});
	}

	if (sidebarOverlay) {
		sidebarOverlay.addEventListener('click', closeSidebar);
	}

	document.addEventListener('click', function(e) {
		const btn = e.target.closest('#themeToggle');
		if (btn) {
			const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
			if (isDark) {
				document.documentElement.removeAttribute('data-theme');
				localStorage.setItem('theme-mode', 'light');
				return;
			}
			document.documentElement.setAttribute('data-theme', 'dark');
			localStorage.setItem('theme-mode', 'dark');
			return;
		}

		if (e.target.closest('.sidebar-link')) {
			closeSidebar();
		}
	});

	window.addEventListener('resize', function() {
		if (window.innerWidth > 992) {
			closeSidebar();
		}
	});
})();
</script>
</body>
</html>
