%define name faxmaster
%define release 1
%define install_dir /var/www/html/hub/mod/faxmaster

Summary:   FaxMaster
Name:      %{name}
Version:   %{version}
Release:   %{release}
License:   GPL
Group:     Development/PHP
URL:       http://phpwebsite.appstate.edu
Source:    %{name}-%{version}.tar.bz2
Requires:  php >= 5.0.0, php-gd >= 5.0.0, phpwebsite
BuildArch: noarch

%description
Web Interface for Aggregating and Browsing Faxes

%prep
%setup -n faxmaster

%post
/sbin/service httpd restart

%install
mkdir -p "$RPM_BUILD_ROOT%{install_dir}"
cp -r * $RPM_BUILD_ROOT%{install_dir}

%clean
rm -rf "$RPM_BUILD_ROOT%install_dir"

%files
%defattr(-,apache,apache)
%{install_dir}

%changelog
* Fri May 11 2012 Jeff Tickle <jtickle@tux.appstate.edu>
- Initial RPM for Faxmaster
