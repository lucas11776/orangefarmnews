<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class News extends CI_Controller
{
  /**
   * News Page View
   *
   * @param   string
   * @param   array
   * @return  void
   */
  private function view(string $page, array $details)
  {
    $this->load->view('template/navbar', $details);
    $this->load->view('news/'.$page, $details);
    $this->load->view('template/footer', $details);
  }

  /**
   * @Route (news)
   */
  public function index()
  {
    # get number of news in database
    $total = $this->news->count();

    # number of result to be show per page
    $per_page = 6;

    # config pagination
    $this->custom_pagination->user_pagination($total, $per_page);

    # get page page
    $page = is_numeric($this->input->get('page')) ? $this->input->get('page') : 0;

    # page details
    $page_details = array(
      'title'            => 'Get the latest news from Orange Farm and surounding areas.',
      'description'      => 'At Orange Farm News we try to give you the latest news so you can stay up to date '.
                            'with what happing around Orange Farm.',
      'active'           => 'news',
      'navbar_adv'       => false,
      'news'             => $this->news->latest($per_page, $page),
      'most_viewed'      => $this->news->most_viewed($per_page, $page),
      'most_commented'   => $this->news->most_viewed(5, $page == 0 ? 0 : $page - 1),
      'most_viewed_blog' => $this->blog->most_viewed(5, $page == 0 ? 0 : $page - 1)
    );

    # page
    $this->view('index', $page_details);
  }

  /**
   * @Route (news/:slug)
   */
  public function single($slug)
  {
    # get news by slug
    $single_news = $this->news->view($slug);

    # check if news item exist
    if($single_news === false)
    {
      # 404 news not found page
      $page_details = array(
        'title'       => '404 News Not Found',
        'message'     => 'News not found they maybe deleted by editor.',
        'description' => 'News not found they maybe deleted by editor.',
        'icon'        => 'fa fa-newspaper-o',
        'active'      => '',
        'navbar_adv'  => false
      );

      # go back one dir to 404 page error
      $this->view('../404', $page_details);

      return;
    }

    # page details
    $page_details = array(
      'title'       => $single_news['title'],
      'description' => word_limiter(strip_tags($single_news['post']), 40),
      'active'      => 'news',
      'single_news' => $single_news,
      'latest_news' => $this->news->latest(8),
      'local_news'  => $this->news_api->local_news(2),
      'most_viewed' => $this->news->most_viewed(5),
      'comments'    => $this->news_comments->get(array('news_id' => $single_news['id'])),
      'navbar_adv'  => false,
      'picture'     => $single_news['picture']
    );

    # page
    $this->view('single', $page_details);
  }

  /**
   * @Route (news)
   */
  public function category($category)
  {
    # check if category exist
    if(in_array(strtolower($category), $this->news::CATEGORY) === false)
    {
      # add page 404 error messages
      $page_details = array(
        'icon'        => 'fa fa-newspaper-o',
        'description' => 'Category you are trying to view does not exist.',
        'title'       => 'News category your selected does not exist.',
        'message'     => 'Please do not change url manual site will handle url change.',
        'active'      => '404',
        'navbar_adv'  => false
      );

      # 404 page not found
      $this->view('../404', $page_details);
    }

    # get number of news in database
    $total = $this->news->count('where', array('category' => $category));

    # number of result to be show per page
    $per_page = 7;

    # config pagination
    $this->custom_pagination->user_pagination($total, $per_page);

    # get page page
    $page = is_numeric($this->input->get('page')) ? $this->input->get('page') : 0;

    # page details
    $page_details = array(
      'title'           => 'OrangeFarmNews ' . $category . ' news category.',
      'description'     => null, # defualt description
      'active'          => strtolower('category-' . $category),
      'navbar_adv'      => false,
      'category_result' => $this->news->get(array('category' => $category), $per_page, $page),
      'most_commented'  => $this->news->most_commented(6, $page),
      'latest_blog'     => $this->blog->latest($per_page - 2, $page >! $per_page ? $page - 1 : $page)
    );

    # page
    $this->view('category', $page_details);
  }

}
