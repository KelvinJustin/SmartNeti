<div class="smartneti-card-row">
    {if in_array($_admin['user_type'],['SuperAdmin','Admin', 'Report'])}
        <div class="smartneti-card">
            <div class="smartneti-card-header">
                <div class="smartneti-card-icon smartneti-card-icon-primary">
                    <i class="ion ion-clock"></i>
                </div>
                <span class="smartneti-card-label">{Lang::T('Income Today')}</span>
            </div>
            <div class="smartneti-card-body">
                <h4 class="smartneti-card-value"><sup>{$_c['currency_code']}</sup>
                    {number_format($iday,0,$_c['dec_point'],$_c['thousands_sep'])}</h4>
            </div>
            <a href="{Text::url('reports/by-date')}" class="smartneti-card-footer">View Details</a>
        </div>
        <div class="smartneti-card">
            <div class="smartneti-card-header">
                <div class="smartneti-card-icon smartneti-card-icon-secondary">
                    <i class="ion ion-android-calendar"></i>
                </div>
                <span class="smartneti-card-label">{Lang::T('Income This Month')}</span>
            </div>
            <div class="smartneti-card-body">
                <h4 class="smartneti-card-value"><sup>{$_c['currency_code']}</sup>
                    {number_format($imonth,0,$_c['dec_point'],$_c['thousands_sep'])}</h4>
            </div>
            <a href="{Text::url('reports/by-period')}" class="smartneti-card-footer">View Details</a>
        </div>
    {/if}
    <div class="smartneti-card">
        <div class="smartneti-card-header">
            <div class="smartneti-card-icon smartneti-card-icon-tertiary">
                <i class="ion ion-person"></i>
            </div>
            <span class="smartneti-card-label">{Lang::T('Active')}/{Lang::T('Expired')}</span>
        </div>
        <div class="smartneti-card-body">
            <h4 class="smartneti-card-value">{$u_act}/{$u_all-$u_act}</h4>
        </div>
        <a href="{Text::url('plan/list')}" class="smartneti-card-footer">View Plans</a>
    </div>
    <div class="smartneti-card">
        <div class="smartneti-card-header">
            <div class="smartneti-card-icon smartneti-card-icon-info">
                <i class="ion ion-android-people"></i>
            </div>
            <span class="smartneti-card-label">{Lang::T('Customers')}</span>
        </div>
        <div class="smartneti-card-body">
            <h4 class="smartneti-card-value">{$c_all}</h4>
        </div>
        <a href="{Text::url('customers/list')}" class="smartneti-card-footer">View Customers</a>
    </div>
</div>