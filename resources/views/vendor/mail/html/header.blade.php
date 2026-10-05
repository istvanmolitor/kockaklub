@props(['url'])
<tr>
<td class="header-bar">&nbsp;</td>
</tr>
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
{!! $slot !!}
</a>
</td>
</tr>
