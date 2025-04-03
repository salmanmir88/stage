<?php
$orderInfo              = $block->getOrderSearch();

$isPending = false;
$isProcessing = false;
$hasOnlyPreorderItem = false;
$hasSomePreorderItem = false;
$hasNoPreorderItem = false;
$isEligibleForStoreCredit = false;
$isShipped = false;
$isReturned = false;
$isOnHold = false;
$isCanceled = false;
$isWaitingForConfirmation = false;
$isCompleted = false;
$hasTrackingLink = false;


if ($orderInfo->getStatus() == 'pending') {
	$isPending = true;
}
if (($orderInfo->getStatus() == 'processing') && $block->getOnlyPreorderItem($orderId)) {
	$isPending = true;
	$isProcessing = true;
	$hasOnlyPreorderItem = true;
}
if (($orderInfo->getStatus() == 'processing') && $block->getNoPreorderItem($orderId)) {
	$isPending = true;
	$isProcessing = true;
	$hasNoPreorderItem = true;
}
if (($orderInfo->getStatus() == 'processing') && $block->getSomePreorderItem($orderId)) {
	$isPending = true;
	$isProcessing = true;
	$hasSomePreorderItem = true;
}
if (($orderInfo->getStatus() == 'processing') && $block->getNoPreorderItem($orderId) && $block->getEligibleForStoreCredit($orderId)) {
	$isPending = true;
	$isProcessing = true;
	$hasNoPreorderItem = true;
	$isEligibleForStoreCredit = true;
}
if ($orderInfo->getStatus() == 'shipped') {
	$isPending = true;
	$isProcessing = true;
	$isShipped = true;
}
if ($orderInfo->getStatus() == 'returned') {
	$isPending = true;
	if ($orderInfo->getInvoice()) {
		$isProcessing = true;
	}
	if ($orderInfo->getShipment()) {
		$isShipped = true;
	}
	$isReturned = true;
}

$trackLink = $block->getTrackingNumberFrom($orderInfo);
?>

<section>
	<div class="container-search-box">
		<div class="prodict-itam-track">
			<div class="order-tracker">

				<?php if ($isPending === true) { ?>
					<div class="step active">
						<div class="icon"><img
								src="<?php echo $block->getMediaUrl() . "box-add.svg" ?>">
						</div>
						<span>Order Created</span>
					</div>
				<?php } else { ?>
					<div class="step active-cancel">
						<div class="icon">
							<img src="<?php echo $block->getMediaUrl() . "danger.svg" ?>">
						</div>
						<span>Order Created</span>
					</div><?php } ?>
				<div class="step">
					<div class="icon"><img
							src="<?php echo $block->getMediaUrl() . "3d-rotate.svg"
									?>"></div>
					<span>In Processing</span>
				</div>
				<div class="step">
					<div class="icon"><img
							src="<?php echo $block->getMediaUrl() . "group.svg" ?>"></div>
					<span>With Courier</span>
				</div>
				<div class="step">
					<div class="icon"><img
							src="<?php echo $block->getMediaUrl() . "box-tick.svg"
									?>"></div>
					<span>Delivered</span>
				</div>
			</div>
			<div class="prodict-itam-track col-sm-12 d-flex">
				<div class="col-sm-6">
					<div class="track-mt-20 scroll-box">
						<table class="pure-table pure-table-bordered">
							<thead>
								<tr>
									<th colspan="2"><?php echo __('Shipping details') ?></th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td class="update"><?php echo __('Order Updated') ?><br>
										<?php echo date('d-M-Y', safe_strtotime($orderInfo->getCreatedAt())); ?></td>
									<td><?php echo __('We are happy to receive your order! Our team is currently working on preparing your order. We will do our best to deliver it to you as soon as possible') ?></td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
				<div class="col-sm-6">
					<div class="track-mt-20 scroll-box">
						<table class="pure-table pure-table-bordered">
							<thead>
								<tr>
									<th colspan="2"><?php echo __('Order Number:# ' . $orderInfo->getEntityId()) ?></th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>
										<div class="prodict-itam-track">
											<?php foreach ($orderInfo->getAllItems() as $_item): ?>
												<?php $_product = $block->getProductById($_item->getProductId()); ?>
												<div class="row">
													<div class="col-md-2 col-sm-1 image-container">
														<div class="product-image">
															<img src="<?php echo $block->getProductImageUrl($_item->getProductId()); ?>" class="product-image-track circle-image">
														</div>
													</div>
													<div class="col-md-10 col-sm-11 content-box-main">
														<div class="row">
															<div class="col-md-12">
																<div class="row">
																	<div class="col-md-8">
																		<span class="float-left mobile-margin "><b><?php echo $_item->getName() ?></b></span>
																		<span class="float-left release-date"><?php echo __('Release Date: ') . date('d/m/Y', safe_strtotime($_product->getReleaseDate())) ?></span>
																	</div>
																	<div class="col-md-4 price">
																		<span class="float-right"><b><?php echo __('SAR') . ' ' . $_item->getPrice(); ?></b></span>
																		<span class="float-right"><?php echo __('Quantity: ') . $_item->getQtyOrdered(); ?></span>
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
											<?php endforeach; ?>
										</div>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<div class="track-mt-20 text-left">
				<p><b><?php echo __('IMPORTANT NOTICE FOR ORDER TRACKING</b>-The tracking order link will be automatically created once your order is delivered to the courier') ?></b></p>
			</div>

		</div>
	</div>
</section>
<?php
function safe_strtotime($datetime)
{
	if (is_string($datetime) && !empty($datetime)) {
		$timestamp = strtotime($datetime);
		if ($timestamp !== false) {
			return $timestamp;
		}
	}
	return null; // or return false, or a default timestamp, as per your needs
}
?>