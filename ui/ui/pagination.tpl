{if $paginator}
    <center>
    <nav class="halo-pagination" aria-label="Pagination">
        <button class="halo-page-step" type="button" {if empty($paginator['prev'])}disabled{/if} onclick="window.location.href='{$paginator['url']}{$paginator['prev']}'" aria-label="Previous page">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="m15 18-6-6 6-6"></path>
            </svg>
        </button>
        {foreach $paginator['pages'] as $page}
            {if $page == '...'}
                <span class="halo-page-gap" aria-hidden="true">…</span>
            {else}
                <button class="halo-page-num {if $paginator['page'] == $page}is-active{/if}" type="button" onclick="window.location.href='{$paginator['url']}{$page}'" {if $paginator['page'] == $page}aria-current="page"{/if}>
                    {$page}
                </button>
            {/if}
        {/foreach}
        <button class="halo-page-step" type="button" {if $paginator['page']>=$paginator['count']}disabled{/if} onclick="window.location.href='{$paginator['url']}{$paginator['next']}'" aria-label="Next page">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="m9 18 6-6-6-6"></path>
            </svg>
        </button>
    </nav>
    </center>
{/if}