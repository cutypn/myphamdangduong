<?php
/**
 * Corporate homepage.
 *
 * @package Bizrise_DDG
 */
if (!defined('ABSPATH')) { exit; }

get_header();

$front_id = (int) get_option('page_on_front');
$hero_image = $front_id ? get_the_post_thumbnail_url($front_id, 'full') : '';
$hero_style = $hero_image ? ' style="--ddg-hero-image:url(\''.esc_url($hero_image).'\')"' : '';
?>
<main id="primary" class="ddg-main ddg-home">
  <section class="ddg-home-hero<?php echo $hero_image ? ' has-image' : ''; ?>"<?php echo $hero_style; ?>>
    <div class="ddg-home-hero__overlay"></div>
    <div class="ddg-container ddg-home-hero__inner">
      <div class="ddg-home-hero__copy">
        <p class="ddg-eyebrow"><?php esc_html_e('Đăng Dương Group', 'bizrise-ddg'); ?></p>
        <h1><?php esc_html_e('Đăng Dương Group — Nâng tầm nhan sắc Việt', 'bizrise-ddg'); ?></h1>
        <p class="ddg-lead"><?php esc_html_e('Đăng Dương Group hướng tới một hệ sinh thái nơi thương hiệu, sản phẩm, kiến thức chăm sóc và cơ hội hợp tác được kết nối trong một trải nghiệm rõ ràng hơn cho người Việt.', 'bizrise-ddg'); ?></p>
        <div class="ddg-home-actions">
          <a class="ddg-btn ddg-btn--light" href="<?php echo esc_url(home_url('/ve-dang-duong/')); ?>"><?php esc_html_e('Khám phá Đăng Dương', 'bizrise-ddg'); ?></a>
          <a class="ddg-text-link ddg-text-link--light" href="<?php echo esc_url(home_url('/nang-luc/')); ?>"><?php esc_html_e('Tìm hiểu năng lực', 'bizrise-ddg'); ?> →</a>
        </div>
      </div>
    </div>
  </section>

  <section class="ddg-home-section">
    <div class="ddg-container ddg-home-split">
      <div>
        <p class="ddg-eyebrow"><?php esc_html_e('Về Đăng Dương', 'bizrise-ddg'); ?></p>
        <h2><?php esc_html_e('Một hệ sinh thái cùng đồng hành với vẻ đẹp Việt', 'bizrise-ddg'); ?></h2>
      </div>
      <div class="ddg-home-copy">
        <p><?php esc_html_e('Đăng Dương Group phát triển các điểm chạm từ câu chuyện doanh nghiệp, thương hiệu và sản phẩm đến kiến thức chăm sóc và kết nối đối tác. Mục tiêu không chỉ là giới thiệu nhiều lựa chọn hơn, mà là giúp mỗi lựa chọn trở nên dễ hiểu và phù hợp hơn với nhu cầu thực tế.', 'bizrise-ddg'); ?></p>
        <p><?php esc_html_e('Chúng tôi tin rằng một trải nghiệm làm đẹp tốt bắt đầu từ sự thấu hiểu: hiểu điều mình đang cần, hiểu vai trò của từng bước chăm sóc và hiểu đâu là lựa chọn phù hợp trước khi quyết định.', 'bizrise-ddg'); ?></p>
        <a class="ddg-text-link" href="<?php echo esc_url(home_url('/ve-dang-duong/')); ?>"><?php esc_html_e('Về Đăng Dương Group', 'bizrise-ddg'); ?> →</a>
      </div>
    </div>
  </section>

  <section class="ddg-home-section ddg-home-section--soft">
    <div class="ddg-container">
      <div class="ddg-home-heading">
        <p class="ddg-eyebrow"><?php esc_html_e('Năng lực & hợp tác', 'bizrise-ddg'); ?></p>
        <h2><?php esc_html_e('Từ mục tiêu đến một hướng phát triển rõ ràng', 'bizrise-ddg'); ?></h2>
        <p><?php esc_html_e('Mỗi dự án cần bắt đầu bằng một mục tiêu rõ ràng. Đăng Dương Group kết nối các nhu cầu nghiên cứu, phát triển sản phẩm, sản xuất và hợp tác thương hiệu theo phạm vi được xác nhận cho từng dự án.', 'bizrise-ddg'); ?></p>
      </div>
      <div class="ddg-home-cardgrid">
        <article class="ddg-home-card">
          <span>01</span>
          <h3><?php esc_html_e('Nghiên cứu & phát triển', 'bizrise-ddg'); ?></h3>
          <p><?php esc_html_e('Từ nhu cầu người dùng và định hướng sản phẩm, quá trình phát triển cần làm rõ vai trò, trải nghiệm mong muốn và các tiêu chí cần đánh giá.', 'bizrise-ddg'); ?></p>
          <a class="ddg-text-link" href="<?php echo esc_url(home_url('/nghien-cuu-phat-trien/')); ?>"><?php esc_html_e('Khám phá R&D', 'bizrise-ddg'); ?> →</a>
        </article>
        <article class="ddg-home-card">
          <span>02</span>
          <h3><?php esc_html_e('Sản xuất & chất lượng', 'bizrise-ddg'); ?></h3>
          <p><?php esc_html_e('Không gian sản xuất, quy trình và thông tin chất lượng được trình bày theo hồ sơ hiện hành, ưu tiên sự rõ ràng thay vì những con số chưa có nguồn xác minh.', 'bizrise-ddg'); ?></p>
          <a class="ddg-text-link" href="<?php echo esc_url(home_url('/nha-may-san-xuat-my-pham/')); ?>"><?php esc_html_e('Xem năng lực sản xuất', 'bizrise-ddg'); ?> →</a>
        </article>
        <article class="ddg-home-card">
          <span>03</span>
          <h3><?php esc_html_e('OEM / ODM mỹ phẩm', 'bizrise-ddg'); ?></h3>
          <p><?php esc_html_e('Mỗi mô hình hợp tác có mức độ chủ động khác nhau về ý tưởng, phát triển và thương hiệu. Bước đầu tiên là làm rõ mục tiêu để chọn hướng phù hợp.', 'bizrise-ddg'); ?></p>
          <a class="ddg-text-link" href="<?php echo esc_url(home_url('/oem-odm-my-pham/')); ?>"><?php esc_html_e('Tìm hiểu OEM / ODM', 'bizrise-ddg'); ?> →</a>
        </article>
      </div>
    </div>
  </section>

  <section class="ddg-home-section">
    <div class="ddg-container ddg-home-split ddg-home-split--brand">
      <div>
        <p class="ddg-eyebrow"><?php esc_html_e('Thương hiệu', 'bizrise-ddg'); ?></p>
        <h2><?php esc_html_e('Thương hiệu được xây từ một góc nhìn rõ ràng', 'bizrise-ddg'); ?></h2>
      </div>
      <div class="ddg-home-copy">
        <p><?php esc_html_e('Một thương hiệu đáng nhớ không chỉ cần hình ảnh đẹp. Nó cần một câu chuyện nhất quán, một nhóm nhu cầu đủ rõ và một trải nghiệm đủ gần để người dùng hiểu vì sao thương hiệu tồn tại.', 'bizrise-ddg'); ?></p>
        <p><?php esc_html_e('Khu vực Thương hiệu giới thiệu các brand đã được xác nhận cho public web, đi từ câu chuyện, định hướng chăm sóc đến sản phẩm và routine liên quan.', 'bizrise-ddg'); ?></p>
        <a class="ddg-btn" href="<?php echo esc_url(home_url('/thuong-hieu/')); ?>"><?php esc_html_e('Khám phá hệ thương hiệu', 'bizrise-ddg'); ?></a>
      </div>
    </div>
  </section>

  <section class="ddg-home-section ddg-home-section--wine">
    <div class="ddg-container ddg-home-split">
      <div>
        <p class="ddg-eyebrow"><?php esc_html_e('Sản phẩm & Routine', 'bizrise-ddg'); ?></p>
        <h2><?php esc_html_e('Bắt đầu từ điều bạn đang cần', 'bizrise-ddg'); ?></h2>
      </div>
      <div class="ddg-home-copy">
        <p><?php esc_html_e('Không cần bắt đầu bằng một danh sách sản phẩm dài. Hãy bắt đầu từ nhu cầu chăm sóc và bối cảnh sử dụng.', 'bizrise-ddg'); ?></p>
        <div class="ddg-home-pills">
          <a href="<?php echo esc_url(home_url('/san-pham/')); ?>"><?php esc_html_e('Dưỡng sáng & đều màu', 'bizrise-ddg'); ?></a>
          <a href="<?php echo esc_url(home_url('/san-pham/')); ?>"><?php esc_html_e('Chăm sóc da có xu hướng nổi mụn', 'bizrise-ddg'); ?></a>
          <a href="<?php echo esc_url(home_url('/san-pham/')); ?>"><?php esc_html_e('Chống nắng', 'bizrise-ddg'); ?></a>
          <a href="<?php echo esc_url(home_url('/san-pham/')); ?>"><?php esc_html_e('Chăm sóc dấu hiệu lão hóa', 'bizrise-ddg'); ?></a>
          <a href="<?php echo esc_url(home_url('/san-pham/')); ?>"><?php esc_html_e('Chăm sóc body', 'bizrise-ddg'); ?></a>
        </div>
        <a class="ddg-btn ddg-btn--light" href="<?php echo esc_url(home_url('/san-pham/')); ?>"><?php esc_html_e('Khám phá Sản phẩm & Routine', 'bizrise-ddg'); ?></a>
      </div>
    </div>
  </section>

  <section class="ddg-home-section">
    <div class="ddg-container">
      <div class="ddg-home-heading">
        <p class="ddg-eyebrow"><?php esc_html_e('Kiến thức', 'bizrise-ddg'); ?></p>
        <h2><?php esc_html_e('Hiểu trước khi lựa chọn', 'bizrise-ddg'); ?></h2>
        <p><?php esc_html_e('Kiến thức làm đẹp không nên khiến việc chăm sóc trở nên phức tạp hơn. Nội dung tập trung giải thích những câu hỏi thực tế trước khi người đọc quyết định mua thêm sản phẩm.', 'bizrise-ddg'); ?></p>
      </div>
      <div class="ddg-home-cardgrid">
        <article class="ddg-home-card ddg-home-card--knowledge">
          <h3><?php esc_html_e('Hiểu làn da', 'bizrise-ddg'); ?></h3>
          <p><?php esc_html_e('Nhận diện nhu cầu từ những biểu hiện và bối cảnh thường gặp trong đời sống hằng ngày.', 'bizrise-ddg'); ?></p>
        </article>
        <article class="ddg-home-card ddg-home-card--knowledge">
          <h3><?php esc_html_e('Thành phần mỹ phẩm', 'bizrise-ddg'); ?></h3>
          <p><?php esc_html_e('Hiểu thành phần theo vai trò và bối cảnh sử dụng, không thần thánh hóa một hoạt chất đơn lẻ.', 'bizrise-ddg'); ?></p>
        </article>
        <article class="ddg-home-card ddg-home-card--knowledge">
          <h3><?php esc_html_e('Routine & cách dùng', 'bizrise-ddg'); ?></h3>
          <p><?php esc_html_e('Sắp xếp các bước chăm sóc theo mục tiêu, thời điểm và khả năng duy trì.', 'bizrise-ddg'); ?></p>
        </article>
      </div>
      <div class="ddg-home-more"><a class="ddg-text-link" href="<?php echo esc_url(home_url('/kien-thuc/')); ?>"><?php esc_html_e('Vào khu vực Kiến thức', 'bizrise-ddg'); ?> →</a></div>
    </div>
  </section>

  <section class="ddg-home-cta">
    <div class="ddg-container ddg-home-split">
      <div>
        <p class="ddg-eyebrow"><?php esc_html_e('Đối tác', 'bizrise-ddg'); ?></p>
        <h2><?php esc_html_e('Cùng tạo nên bước phát triển tiếp theo', 'bizrise-ddg'); ?></h2>
      </div>
      <div class="ddg-home-copy">
        <p><?php esc_html_e('Đăng Dương Group kết nối các nhu cầu phân phối, đại lý, affiliate và hợp tác phát triển mỹ phẩm trong một hành trình trao đổi rõ ràng hơn.', 'bizrise-ddg'); ?></p>
        <div class="ddg-home-actions">
          <a class="ddg-btn ddg-btn--light" href="<?php echo esc_url(home_url('/doi-tac/')); ?>"><?php esc_html_e('Trở thành đối tác', 'bizrise-ddg'); ?></a>
          <a class="ddg-text-link ddg-text-link--light" href="<?php echo esc_url(home_url('/lien-he/')); ?>"><?php esc_html_e('Liên hệ Đăng Dương', 'bizrise-ddg'); ?> →</a>
        </div>
      </div>
    </div>
  </section>
</main>
<?php get_footer();
