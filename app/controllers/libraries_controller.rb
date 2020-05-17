class LibrariesController < ApplicationController

  def show
    @plex_token = SystemSetting.find_by_name("plex_token").value
    @plex_url = SystemSetting.find_by_name("plex_url").value
    @library_entrys = LibraryEntry.where(library_id: params[:id])
  end
end
