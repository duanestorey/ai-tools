class User < ApplicationRecord
  has_many :posts, dependent: :destroy
  has_many :comments, dependent: :destroy
  has_one :profile, dependent: :destroy
  
  validates :email, presence: true, uniqueness: true
  validates :username, presence: true, length: { minimum: 3, maximum: 20 }
  validates :password, presence: true, length: { minimum: 8 }
  
  before_save :downcase_email
  
  scope :active, -> { where(active: true) }
  scope :admins, -> { where(admin: true) }
  
  def full_name
    "#{first_name} #{last_name}"
  end
  
  private
  
  def downcase_email
    self.email = email.downcase if email.present?
  end
end
