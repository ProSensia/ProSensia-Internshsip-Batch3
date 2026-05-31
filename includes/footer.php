  </div>
</div>
<footer class="app-footer">
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
    <div class="muted" style="font-size:12px">
      © <?= date('Y') ?> ProSensia · <b><?= e(founder_name()) ?></b> — <?= e(founder_title()) ?>
    </div>
    <div class="muted" style="font-size:12px">
      EasyPaisa — <b>Momin Khan</b> · 0310-7717890
    </div>
  </div>
</footer>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function(){
  const sb=document.querySelector('.sidebar'), bd=document.querySelector('.sidebar-backdrop');
  document.querySelectorAll('[data-sidebar-open]').forEach(b=>b.addEventListener('click',()=>{sb.classList.add('open');bd.classList.add('show');}));
  document.querySelectorAll('[data-sidebar-close]').forEach(b=>b.addEventListener('click',()=>{sb.classList.remove('open');bd.classList.remove('show');}));
})();
</script>
</body></html>
