package za.ac.cput.Repository;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;
import za.ac.cput.Domain.Publisher;

@Repository
public interface PublisherRepository extends JpaRepository<Publisher, Long> {
}
