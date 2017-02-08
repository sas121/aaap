<?php
/**
 * @var $bandwidth_usage array An associative array of bandwidth usage information
 */
?>
<p>
	<b>You have currently used <?php echo number_format( $bandwidth_usage['usage'], 0 ); ?>Gb of bandwidth for the month.</b>
</p>

<p>
	You are projected to exceed your bandwidth allocation for your site/server.
	If your bandwidth usage exceeds your plan allocation, you will be responsible for any bandwidth overage charges accrued.
	Your account will be charged for the extra bandwidth usage at our cost of $0.15 per Gb of bandwidth used.
</p>

<p style="color: red">
	If your bandwidth usage continues at this rate
	<b>
		you will exceed your <?php echo $bandwidth_usage['allotment']; ?>Gb monthly allocation by
		<?php echo number_format($bandwidth_usage['projected_difference'], 0); ?>Gb (<?php echo number_format($bandwidth_usage['projected_difference_percent'], 0); ?>%).
		The projected overage cost on this is $<?php echo number_format($bandwidth_usage['projected_cost'], 2); ?>.
	</b>
</p>

<p>
	The bandwidth usage and overage cost are subject to change, and will be verified before any charges are applied to your account.
	If you feel that this bandwidth usage will continue at the present rate, please make changes to the site configuration to reduce bandwidth usage or upgrade your plan.
</p>

<p>
	If you need help reducing your bandwidth usage or upgrading your Synthesis hosting plan please contact support
	by submitting a ticket at <a href="https://accounts.websynthesis.com">https://accounts.websynthesis.com</a>
</p>

