  </div>
</div>

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
