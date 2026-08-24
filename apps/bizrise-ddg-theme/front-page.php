<?php
/**
 * Corporate homepage.
 *
 * @package Bizrise_DDG
 */
if (!defined('ABSPATH')) { exit; }

get_header();

$front_id   = (int) get_option('page_on_front');
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
        <p class="ddg-lead"><?php esc_html_e('Kết nối thương hiệu, sản phẩm chăm sóc, kiến thức làm đẹp và cơ hội hợp tác trong một hệ sinh thái được xây dựng dành cho người Việt.', 'bizrise-ddg'); ?></p>
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
        <p><?php esc_html_e('Đăng Dương Group phát triển các điểm chạm từ câu chuyện doanh nghiệp, thương hiệu và sản phẩm đến kiến thức chăm sóc và kết nối đối tác. Mỗi trải nghiệm được định hướng theo tinh thần rõ ràng, gần gũi và dễ tiếp cận hơn.', 'bizrise-ddg'); ?></p>
        <p><?php esc_html_e('Chúng tôi tin rằng một hành trình làm đẹp tốt bắt đầu từ sự thấu hiểu: hiểu điều mình đang cần, hiểu vai trò của từng bước chăm sóc và hiểu đâu là lựa chọn phù hợp với bản thân.', 'bizrise-ddg'); ?></p>
        <a class="ddg-text-link" href="<?php echo esc_url(home_url('/ve-dang-duong/')); ?>"><?php esc_html_e('Về Đăng Dương Group', 'bizrise-ddg'); ?> →</a>
      </div>
    </div>
  </section>

  <section class="ddg-home-section ddg-home-section--soft">
    <div class="ddg-container">
      <div class="ddg-home-heading">
        <p class="ddg-eyebrow"><?php esc_html_e('Năng lực & hợp tác', 'bizrise-ddg'); ?></p>
        <h2><?php esc_html_e('Từ ý tưởng đến một hướng phát triển rõ ràng', 'bizrise-ddg'); ?></h2>
        <p><?php esc_html_e('Đăng Dương Group kết nối nhu cầu nghiên cứu, phát triển sản phẩm, sản xuất và hợp tác thương hiệu để cùng đối tác xây dựng một lộ trình phù hợp với mục tiêu dự án.', 'bizrise-ddg'); ?></p>
      </div>
      <div class="ddg-home-cardgrid">
        <article class="ddg-home-card">
          <span>01</span>
          <h3><?php esc_html_e('Nghiên cứu & phát triển', 'bizrise-ddg'); ?></h3>
          <p><?php esc_html_e('Bắt đầu từ nhu cầu người dùng, định hướng sản phẩm và trải nghiệm mong muốn để hình thành một brief phát triển rõ ràng hơn.', 'bizrise-ddg'); ?></p>
          <a class="ddg-text-link" href="<?php echo esc_url(home_url('/nghien-cuu-phat-trien/')); ?>"><?php esc_html_e('Khám phá R&D', 'bizrise-ddg'); ?> →</a>
        </article>
        <article class="ddg-home-card">
          <span>02</span>
          <h3><?php esc_html_e('Sản xuất & chất lượng', 'bizrise-ddg'); ?></h3>
          <p><?php esc_html_e('Tìm hiểu cách Đăng Dương Group tổ chức hành trình từ định hướng sản phẩm đến các bước sản xuất và kiểm soát chất lượng.', 'bizrise-ddg'); ?></p>
          <a class="ddg-text-link" href="<?php echo esc_url(home_url('/nha-may-san-xuat-my-pham/')); ?>"><?php esc_html_e('Xem năng lực sản xuất', 'bizrise-ddg'); ?> →</a>
        </article>
        <article class="ddg-home-card">
          <span>03</span>
          <h3><?php esc_html_e('OEM / ODM mỹ phẩm', 'bizrise-ddg'); ?></h3>
          <p><?php esc_html_e('Từ ý tưởng ban đầu đến hướng phát triển phù hợp, mỗi dự án OEM/ODM được bắt đầu bằng việc làm rõ mục tiêu, sản phẩm và trải nghiệm mong muốn.', 'bizrise-ddg'); ?></p>
          <a class="ddg-text-link" href="<?php echo esc_url(home_url('/oem-odm-my-pham/')); ?>"><?php esc_html_e('Tìm hiểu OEM / ODM', 'bizrise-ddg'); ?> →</a>
        </article>
      </div>
    </div>
  </section>

  <section class="ddg-home-section">
    <div class="ddg-container ddg-home-split ddg-home-split--brand">
      <div>
        <p class="ddg-eyebrow"><?php esc_html_e('Thương hiệu', 'bizrise-ddg'); ?></p>
        <h2><?php esc_html_e('Mỗi thương hiệu là một câu chuyện riêng', 'bizrise-ddg'); ?></h2>
      </div>
      <div class="ddg-home-copy">
        <p><?php esc_html_e('Một thương hiệu đáng nhớ không chỉ cần hình ảnh đẹp. Nó cần một góc nhìn nhất quán, một nhóm nhu cầu đủ rõ và một trải nghiệm đủ gần để người dùng hiểu mình nên bắt đầu từ đâu.', 'bizrise-ddg'); ?></p>
        <p><?php esc_html_e('Khám phá câu chuyện thương hiệu, định hướng chăm sóc, sản phẩm nổi bật và những routine được gợi ý theo từng nhu cầu.', 'bizrise-ddg'); ?></p>
        <a class="ddg-btn" href="<?php echo esc_url(home_url('/thuong-hieu/')); ?>"><?php esc_html_e('Khám phá thương hiệu', 'bizrise-ddg'); ?></a>
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
        <p><?php esc_html_e('Thay vì bắt đầu bằng một danh sách sản phẩm dài, hãy bắt đầu từ nhu cầu chăm sóc và thói quen hằng ngày của bạn.', 'bizrise-ddg'); ?></p>
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
        <p><?php esc_html_e('Kiến thức làm đẹp nên giúp việc chăm sóc trở nên dễ hiểu hơn. Đăng Dương Journal tập trung vào những câu hỏi thực tế để bạn có thêm cơ sở trước khi lựa chọn sản phẩm hoặc xây routine.', 'bizrise-ddg'); ?></p>
      </div>
      <div class="ddg-home-cardgrid">
        <article class="ddg-home-card ddg-home-card--knowledge">
          <h3><?php esc_html_e('Hiểu làn da', 'bizrise-ddg'); ?></h3>
          <p><?php esc_html_e('Nhận diện nhu cầu từ những biểu hiện và bối cảnh thường gặp trong đời sống hằng ngày.', 'bizrise-ddg'); ?></p>
        </article>
        <article class="ddg-home-card ddg-home-card--knowledge">
          <h3><?php esc_html_e('Thành phần mỹ phẩm', 'bizrise-ddg'); ?></h3>
          <p><?php esc_html_e('Hiểu thành phần theo vai trò và bối cảnh sử dụng thay vì chỉ chạy theo một thành phần nổi bật.', 'bizrise-ddg'); ?></p>
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
        <p><?php esc_html_e('Đăng Dương Group kết nối các nhu cầu phân phối, đại lý, affiliate và hợp tác phát triển mỹ phẩm bằng một hành trình trao đổi rõ ràng, thực tế và phù hợp với từng mục tiêu.', 'bizrise-ddg'); ?></p>
        <div class="ddg-home-actions">
          <a class="ddg-btn ddg-btn--light" href="<?php echo esc_url(home_url('/doi-tac/')); ?>"><?php esc_html_e('Trở thành đối tác', 'bizrise-ddg'); ?></a>
          <a class="ddg-text-link ddg-text-link--light" href="<?php echo esc_url(home_url('/lien-he/')); ?>"><?php esc_html_e('Liên hệ Đăng Dương', 'bizrise-ddg'); ?> →</a>
        </div>
      </div>
    </div>
  </section>
</main>
<?php get_footer();
