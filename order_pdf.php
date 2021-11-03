<?php
include('vendor/autoload.php');
require('connection.inc.php');
require('functions.inc.php');

if(!$_SESSION['ADMIN_LOGIN']){
	if(!isset($_SESSION['USER_ID'])){
		die();
	}
}

$order_id=get_safe_value($con,$_GET['id']);

$css=file_get_contents('css/bootstrap.min.css');
$css.=file_get_contents('style.css');

$html='<div class="wishlist-table table-responsive">
   <table>
      <thead>
         <tr>
            <th class="product-thumbnail">Product Name</th>
            <th class="product-thumbnail">Product Image</th>
            <th class="product-name">Qty</th>
            <th class="product-price">Total Price</th>
         </tr>
      </thead>
      <tbody>';

		if(isset($_SESSION['ADMIN_LOGIN'])){
			$res=mysqli_query($con,"select distinct(order_detail.id) ,order_detail.*,product.name,product.image from order_detail,product ,`order` where order_detail.order_id='$order_id' and order_detail.product_id=product.id");
		}else{
			$uid=$_SESSION['USER_ID'];
			$res=mysqli_query($con,"select distinct(order_detail.id) ,order_detail.*,product.name,product.image from order_detail,product ,`order` where order_detail.order_id='$order_id' and `order`.user_id='$uid' and order_detail.product_id=product.id");
		}

		$total_price=0;
		if(mysqli_num_rows($res)==0){
			die();
		}
		while($row=mysqli_fetch_assoc($res)){
			$gst=0.18;
		$total_price=$total_price+($row['qty']*$row['price']);
		 $pp=$row['qty']*$row['price'];
		 $gst_amt=$pp*$gst;
		 $total_amt=$pp+$gst_amt;
         $html.='<tr>
            <td class="product-name">'.$row['name'].'</td>
            <td class="product-name"> <img src="media/product/'.$row['image'].'" style="min-wisth:150px;min-height:150px;max-wisth:150px;max-height:150px;"></td>
            <td class="product-name">'.$row['qty'].'</td>
            <td class="product-name">'.$pp.'</td>
         </tr>';
		 }
		 $html.='
		 <tr>
		 	 <td colspan="2"></td>
		 	 <td class="product-name">Total Cost</td>
		 	 <td class="product-name">'.$total_price.'</td>

		  </tr>
			<tr>
 				<td colspan="2"></td>
 				<td class="product-name">12% GST</td>
 				<td class="product-name">'.$gst_amt.'</td>

 			</tr>
		 <tr>
				<td colspan="2"></td>
				<td class="product-name">Total Price</td>
				<td class="product-name">'.$total_amt.'</td>

			</tr>

			';

      $html.='</tbody>
   </table>
</div>';
$mpdf=new \Mpdf\Mpdf();
$mpdf->WriteHTML($css,1);
$mpdf->WriteHTML($html,2);
$file=time().'.pdf';
$mpdf->Output($file,'D');
?>
