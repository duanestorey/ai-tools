Rails.application.routes.draw do
  # Define your application routes per the DSL in https://guides.rubyonrails.org/routing.html

  # Defines the root path route ("/")
  root "home#index"
  
  resources :users
  resources :posts
  
  get '/about', to: 'pages#about'
  get '/contact', to: 'pages#contact'
  
  namespace :admin do
    resources :dashboard
    resources :users
  end
  
  # API routes
  namespace :api do
    namespace :v1 do
      resources :users, only: [:index, :show, :create]
      resources :posts, only: [:index, :show]
    end
  end
end
