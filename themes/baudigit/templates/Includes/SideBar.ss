<!--start sidebar-->
    <aside class="sidebar-wrapper" data-simplebar="true">
      <div class="sidebar-header">
        <div class="logo-icon">
          <img src="assets/images/logo-icon.png" class="logo-img" alt="">
        </div>
        <div class="logo-name flex-grow-1">
          <h5 class="mb-0">BauDigit</h5>
        </div>
        <div class="sidebar-close">
          <span class="material-icons-outlined">close</span>
        </div>
      </div>
      <div class="sidebar-nav">
          <!--navigation-->
          <ul class="metismenu" id="sidenav">
			<%--
			<li class="menu-label">UI Elements</li>
			--%>
			<% loop $Menu(1) %>
				<li>
					<a href="$Link">
						<div class="menu-title">$MenuTitle.XML</div>
					</a>
				</li>
			<% end_loop %>
           </ul>
          <!--end navigation-->
      </div>
    </aside>
  <!--end sidebar-->