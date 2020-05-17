class ManageController < ApplicationController
  def index
    get_sections
  end

  def add_library
    library  = Library.create(name: params[:title], plex_key: params[:key], library_type: params[:type])
    update_library(library)
    redirect_to manage_index_path
  end

  def update_library(library)
    get_library_data(library.plex_key)
    if @metadata["MediaContainer"]["Directory"] 
      @metadata = @metadata["MediaContainer"]["Directory"]

    else 
      @metadata =  @metadata["MediaContainer"]["Video"]
    end 
    @metadata.each do |item|
      LibraryEntry.create_with(key: item["key"], library_id: library.id).find_or_create_by(rating: item["rating"], title: item["title"], imagepath: item["thumb"])
    end
  end

  def get_library_data(section_key)
    require 'open-uri'
    require 'active_support/core_ext/hash'
    
    @plex_token = SystemSetting.find_by_name("plex_token").value
    @plex_url = SystemSetting.find_by_name("plex_url").value
    xml = Nokogiri::XML(open("#{@plex_url}/library/sections/#{section_key}/all?X-Plex-Token=#{@plex_token}"))

    @metadata = Hash.from_xml(xml.to_s)
  end
  
  def get_sections
    require 'open-uri'
    require 'active_support/core_ext/hash'
    
    plex_token = SystemSetting.find_by_name("plex_token").value
    plex_url = SystemSetting.find_by_name("plex_url").value
    xml = Nokogiri::XML(open("#{plex_url}/library/sections?X-Plex-Token=#{plex_token}"))
    @section_hash = Hash.from_xml(xml.to_s)
  end
  helper_method :get_sections

end
